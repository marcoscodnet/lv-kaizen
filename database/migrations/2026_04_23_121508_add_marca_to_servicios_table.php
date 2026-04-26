<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarcaToServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->unsignedBigInteger('marca_id')->nullable();
            $table->foreign('marca_id')->references('id')->on('marcas');
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->foreign('modelo_id')->references('id')->on('modelos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('servicios', function (Blueprint $table) {
            //
        });
    }
}
