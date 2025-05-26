<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPiezaToPiezaVentaPiezasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pieza_venta_piezas', function (Blueprint $table) {
            $table->unsignedBigInteger('pieza_venta_id')->nullable();
            $table->foreign('pieza_venta_id')->references('id')->on('venta_piezas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pieza_venta_piezas', function (Blueprint $table) {
            //
        });
    }
}
