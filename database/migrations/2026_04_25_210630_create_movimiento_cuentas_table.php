<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientoCuentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimiento_cuentas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entidad_id')->constrained('entidades')->cascadeOnDelete();
            $table->enum('tipo', ['Ingreso', 'Egreso']);
            $table->decimal('monto', 12, 2);
            $table->date('fecha');
            $table->string('concepto')->nullable();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('venta_pieza_id')->nullable()->constrained('venta_piezas')->nullOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->foreignId('pago_id')->nullable()->constrained('pagos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimiento_cuentas');
    }
}
