<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->datetime('carga')->nullable();
            $table->unsignedBigInteger('tipo_servicio_id')->nullable();
            $table->foreign('tipo_servicio_id')->references('id')->on('tipo_servicios');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursals');
            $table->integer('kilometros')->nullable();
            $table->datetime('ingreso')->nullable();
            $table->text('observacion')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('repuestos')->nullable();
            $table->text('mecanicos')->nullable();
            $table->text('instrumentos')->nullable();
            $table->string('tiempo')->nullable();
            $table->datetime('entrega')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->boolean('pagado')->default(false);
            $table->string('modelo')->nullable();
            $table->string('year',10)->nullable();
            $table->string('chasis',50)->nullable();
            $table->string('motor',50)->nullable();
            $table->datetime('venta')->nullable();
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
        Schema::dropIfExists('servicios');
    }
}
