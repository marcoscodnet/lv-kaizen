<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Señas (de motos, repuestos, servicios o lo que sea).
 *
 * El cliente deja una seña y días después se aplica como pago de la operación
 * (venta de moto, venta de artículos o servicio).
 *
 * La seña tiene sus propios pagos (pagos.senia_id), con cualquier forma de pago:
 *  - efectivo y similares: entran a la caja del día y se acreditan solos;
 *  - transferencia, tarjeta, etc.: van a Auditoría como cualquier otro pago.
 * Recién cuando está todo autorizado la seña queda disponible, y vale lo
 * acreditado.
 *
 * Al aplicarla se crea en la operación un pago con forma de pago "Seña"
 * (pagos.senia_aplicada_id): cuenta como cobrado y acreditado, pero no vuelve a
 * tocar caja ni cuenta, porque la plata ya entró el día de la seña.
 */
class CreateSeniasTable extends Migration
{
    public function up()
    {
        Schema::create('senias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('sucursal_id')->constrained('sucursals');
            $table->dateTime('fecha');
            $table->decimal('monto', 12, 2);           // cobrado (suma de sus pagos)
            $table->string('detalle')->nullable();     // para qué es: "Moto X", "Repuesto Y"
            $table->enum('estado', ['Vigente', 'Aplicada', 'Devuelta', 'Retenida'])->default('Vigente');
            // Dónde se aplicó (una sola de las tres)
            $table->unsignedBigInteger('venta_id')->nullable();
            $table->unsignedBigInteger('venta_pieza_id')->nullable();
            $table->unsignedBigInteger('servicio_id')->nullable();
            $table->unsignedBigInteger('pago_aplicado_id')->nullable();
            // Baja
            $table->dateTime('fecha_baja')->nullable();
            $table->unsignedBigInteger('devolucion_entidad_id')->nullable();
            $table->decimal('devolucion_monto', 12, 2)->nullable();
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'estado']);
            $table->index('venta_id');
            $table->index('venta_pieza_id');
            $table->index('servicio_id');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->unsignedBigInteger('senia_id')->nullable();          // pago recibido como seña
            $table->unsignedBigInteger('senia_aplicada_id')->nullable(); // pago de una operación que aplica una seña
            $table->index('senia_id');
            $table->index('senia_aplicada_id');
        });

        Schema::table('movimiento_cajas', function (Blueprint $table) {
            $table->unsignedBigInteger('senia_id')->nullable();
            $table->index('senia_id');
        });

        Schema::table('movimiento_cuentas', function (Blueprint $table) {
            $table->unsignedBigInteger('senia_id')->nullable();
            $table->index('senia_id');
        });

        // Conceptos de caja. Inactivos: los usa el sistema, no se eligen a mano
        // en "Registrar Movimiento".
        if (!DB::table('conceptos')->where('nombre', 'Seña')->exists()) {
            DB::table('conceptos')->insert(['nombre' => 'Seña', 'tipo' => 'Suma', 'activo' => 0, 'created_at' => now(), 'updated_at' => now()]);
        }
        if (!DB::table('conceptos')->where('nombre', 'Devolución de seña')->exists()) {
            DB::table('conceptos')->insert(['nombre' => 'Devolución de seña', 'tipo' => 'Resta', 'activo' => 0, 'created_at' => now(), 'updated_at' => now()]);
        }

        // Forma de pago con la que la seña entra a la operación. Inactiva: no
        // aparece en las filas de cobro. No tangible, sin cuenta y sin
        // autorización: la plata ya se registró (y se auditó) con la seña.
        if (!DB::table('entidads')->where('nombre', 'Seña')->exists()) {
            DB::table('entidads')->insert([
                'nombre' => 'Seña', 'forma' => 'Contado', 'autorizacion' => 0, 'tangible' => 0,
                'cuenta' => 0, 'activa' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [];
        foreach (['senia-listar', 'senia-crear', 'senia-baja', 'senia-otras-sucursales'] as $nombre) {
            $permisos[$nombre] = Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'Administrador')->first();
        if ($admin) {
            $admin->givePermissionTo(array_values($permisos));
        }

        // Quien hoy registra movimientos de caja puede registrar y dar de baja señas
        $permisoCaja = Permission::where('name', 'caja-movimiento-registrar')->first();
        foreach (($permisoCaja ? $permisoCaja->roles : []) as $rol) {
            $rol->givePermissionTo([$permisos['senia-listar'], $permisos['senia-crear'], $permisos['senia-baja']]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down()
    {
        Schema::table('movimiento_cuentas', function (Blueprint $table) {
            $table->dropIndex(['senia_id']);
            $table->dropColumn('senia_id');
        });
        Schema::table('movimiento_cajas', function (Blueprint $table) {
            $table->dropIndex(['senia_id']);
            $table->dropColumn('senia_id');
        });
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['senia_id']);
            $table->dropIndex(['senia_aplicada_id']);
            $table->dropColumn(['senia_id', 'senia_aplicada_id']);
        });
        Schema::dropIfExists('senias');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::whereIn('name', ['senia-listar', 'senia-crear', 'senia-baja', 'senia-otras-sucursales'])->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
