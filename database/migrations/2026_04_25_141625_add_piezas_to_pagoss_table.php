<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPiezasToPagossTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Los pagos ya tienen venta_id y servicio_id — agregar venta_pieza_id
            $table->unsignedBigInteger('venta_pieza_id')->nullable();
            $table->foreign('venta_pieza_id')->references('id')->on('venta_piezas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {

        });
    }
}
