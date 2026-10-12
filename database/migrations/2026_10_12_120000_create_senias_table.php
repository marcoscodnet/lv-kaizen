<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Señas de motos.
 *
 * El cliente seña una moto (muchas veces sin unidad asignada) y la retira días
 * después. La seña entra a la caja del día en que se paga, como un movimiento
 * de caja con concepto "Seña" y un cliente. Cuando se concreta la venta, la seña
 * se aplica como pago de la venta (forma de pago "Seña"): no vuelve a tocar la
 * caja porque la plata ya entró. Si el cliente desiste, la seña se devuelve
 * (egreso en la caja del día) o se retiene.
 *
 * pagos.senia_id marca el pago de una venta que viene de una seña: esos pagos
 * no forman parte de las filas de cobro del formulario y se sincronizan aparte.
 */
class CreateSeniasTable extends Migration
{
    public function up()
    {
        Schema::create('senias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimiento_caja_id')->constrained('movimiento_cajas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('sucursal_id')->constrained('sucursals');
            $table->decimal('monto', 12, 2);
            $table->dateTime('fecha');
            $table->enum('estado', ['Pendiente', 'Aplicada', 'Devuelta', 'Retenida'])->default('Pendiente');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->unsignedBigInteger('pago_id')->nullable();
            $table->unsignedBigInteger('movimiento_devolucion_id')->nullable();
            $table->dateTime('fecha_baja')->nullable();
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'estado']);
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->unsignedBigInteger('senia_id')->nullable();
            $table->index('senia_id');
        });

        // Concepto de caja para registrar la seña y para su devolución.
        // La devolución la genera el sistema: inactiva para que no se use a mano.
        if (!DB::table('conceptos')->where('nombre', 'Seña')->exists()) {
            DB::table('conceptos')->insert(['nombre' => 'Seña', 'tipo' => 'Suma', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }
        if (!DB::table('conceptos')->where('nombre', 'Devolución de seña')->exists()) {
            DB::table('conceptos')->insert(['nombre' => 'Devolución de seña', 'tipo' => 'Resta', 'activo' => 0, 'created_at' => now(), 'updated_at' => now()]);
        }

        // Forma de pago con la que la seña entra a la venta. Inactiva: no aparece
        // en las filas de cobro, la usa solo el sistema al aplicar una seña.
        // No tangible (no toca la caja de hoy), sin cuenta y sin autorización
        // (la plata ya se registró en la caja del día de la seña).
        if (!DB::table('entidads')->where('nombre', 'Seña')->exists()) {
            DB::table('entidads')->insert([
                'nombre' => 'Seña', 'forma' => 'Contado', 'autorizacion' => 0, 'tangible' => 0,
                'cuenta' => 0, 'activa' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Aplicar en una venta señas cobradas en otra sucursal.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $permission = Permission::firstOrCreate(['name' => 'senia-otras-sucursales', 'guard_name' => 'web']);
        $admin = Role::where('name', 'Administrador')->first();
        if ($admin) {
            $admin->givePermissionTo($permission);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['senia_id']);
            $table->dropColumn('senia_id');
        });
        Schema::dropIfExists('senias');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::where('name', 'senia-otras-sucursales')->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
