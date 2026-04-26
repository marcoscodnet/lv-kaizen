<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServicioToMovimientoCajasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimiento_cajas', function (Blueprint $table) {
            $table->unsignedBigInteger('servicio_id')->nullable()->after('venta_id');
            $table->unsignedBigInteger('venta_pieza_id')->nullable()->after('servicio_id');

            $table->foreign('servicio_id')->references('id')->on('servicios')->nullOnDelete();
            $table->foreign('venta_pieza_id')->references('id')->on('venta_piezas')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movimiento_cajas', function (Blueprint $table) {
            //
        });
    }
}
