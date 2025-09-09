<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoToPiezasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('piezas', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_pieza_id')->nullable();
            $table->foreign('tipo_pieza_id')->references('id')->on('tipo_piezas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('piezas', function (Blueprint $table) {
            //
        });
    }
}
