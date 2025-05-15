<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_origen_id')->nullable();
            $table->foreign('sucursal_origen_id')->references('id')->on('sucursals');
            $table->unsignedBigInteger('sucursal_destino_id')->nullable();
            $table->foreign('sucursal_destino_id')->references('id')->on('sucursals');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->datetime('fecha')->nullable();
            $table->text('observaciones')->nullable();
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
        Schema::dropIfExists('movimientos');
    }
}
