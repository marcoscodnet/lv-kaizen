<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proveedors', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('razon')->nullable();
            $table->string('cuil', 13)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('particular_area', 10)->nullable();
            $table->string('particular', 30)->nullable();
            $table->string('celular_area', 10)->nullable();
            $table->string('celular', 30)->nullable();
            $table->enum('iva', ['Responsable Inscripto','Responsable No inscripto','No Inscripto','Monotributista','Consumidor Final'])->nullable();
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
        Schema::dropIfExists('proveedors');
    }
}
