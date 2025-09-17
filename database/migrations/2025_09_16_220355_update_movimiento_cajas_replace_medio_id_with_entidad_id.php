<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMovimientoCajasReplaceMedioIdWithEntidadId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimiento_cajas', function (Blueprint $table) {
            // eliminar columna antigua
            $table->dropForeign(['medio_id']); // si tiene FK
            $table->dropColumn('medio_id');

            // agregar columna nueva
            $table->unsignedBigInteger('entidad_id')->nullable()->after('caja_id');
            $table->foreign('entidad_id')->references('id')->on('entidads');
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
