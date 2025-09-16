<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientoCajasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimiento_cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas');
            $table->foreignId('venta_id')->nullable()->constrained('ventas');
            $table->foreignId('concepto_id')->constrained('conceptos');
            $table->enum('tipo', ['Ingreso', 'Egreso']);
            $table->decimal('monto', 12, 2);
            $table->foreignId('medio_id')->constrained('medios');
            $table->string('referencia')->nullable(); // nro ticket, operación, etc
            $table->boolean('acreditado')->default(true); // si ya está disponible o no
            $table->dateTime('fecha');
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
        Schema::dropIfExists('movimiento_cajas');
    }
}
