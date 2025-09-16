<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCajasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursals');
            $table->foreignId('user_id')->constrained('users'); // quién abre la caja
            $table->dateTime('apertura');
            $table->dateTime('cierre')->nullable();
            $table->decimal('inicial', 12, 2)->default(0);
            $table->decimal('final', 12, 2)->nullable();
            $table->enum('estado', ['Abierta', 'Cerrada'])->default('abierta');
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
        Schema::dropIfExists('cajas');
    }
}
