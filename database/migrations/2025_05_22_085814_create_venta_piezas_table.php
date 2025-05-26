<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentaPiezasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venta_piezas', function (Blueprint $table) {
            $table->id();
            $table->decimal('precio', 10, 2)->nullable();
            $table->decimal('precio_minimo', 10, 2)->nullable();
            $table->string('cliente')->nullable();
            $table->string('documento', 45)->nullable();
            $table->string('telefono', 45)->nullable();
            $table->string('moto')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursals');
            $table->integer('pedido')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->datetime('fecha')->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('destino', ['Salón','Sucursal','Taller'])->nullable();
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
        Schema::dropIfExists('venta_piezas');
    }
}
