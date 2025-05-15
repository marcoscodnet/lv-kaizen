<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePiezasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('piezas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 45)->nullable();
            $table->string('descripcion', 50)->nullable();
            $table->integer('stock_minimo')->nullable();
            $table->decimal('costo', 10, 2)->nullable();
            $table->decimal('precio_minimo', 10, 2)->nullable();
            $table->integer('stock_actual')->nullable();
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
        Schema::dropIfExists('piezas');
    }
}
