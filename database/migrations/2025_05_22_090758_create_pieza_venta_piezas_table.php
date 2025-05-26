<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePiezaVentaPiezasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pieza_venta_piezas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pieza_id')->nullable();
            $table->foreign('pieza_id')->references('id')->on('piezas');
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursals');
            $table->integer('cantidad')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
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
        Schema::dropIfExists('pieza_venta_piezas');
    }
}
