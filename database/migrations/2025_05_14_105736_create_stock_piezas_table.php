<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockPiezasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_piezas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pieza_id')->nullable();
            $table->foreign('pieza_id')->references('id')->on('piezas');
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursals');
            $table->string('remito')->nullable();
            $table->integer('cantidad')->nullable();
            $table->decimal('costo', 10, 2)->nullable();
            $table->decimal('precio_minimo', 10, 2)->nullable();
            $table->enum('proveedor', ['Honda'])->nullable();
            $table->datetime('ingreso')->nullable();
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
        Schema::dropIfExists('stock_piezas');
    }
}
