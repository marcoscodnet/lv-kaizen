<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('documento', 20)->unique();
            $table->string('cuil', 13)->nullable();
            $table->datetime('nacimiento')->nullable();
            $table->enum('estado_civil', ['Soltero/a','Casado/a','Divorciado/a','Concubino/a','Viudo/a'])->nullable();
            $table->string('email', 255)->nullable();
            $table->string('particular_area', 10)->nullable();
            $table->string('particular', 30)->nullable();
            $table->string('celular_area', 10)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('calle', 50)->nullable();
            $table->string('nro', 10)->nullable();
            $table->string('piso', 10)->nullable();
            $table->string('depto', 10)->nullable();
            $table->unsignedBigInteger('localidad_id')->nullable();
            $table->foreign('localidad_id')->references('id')->on('localidads');
            $table->string('cp', 10)->nullable();
            $table->string('nacionalidad')->nullable();
            $table->string('ocupacion')->nullable();
            $table->string('trabajo')->nullable();
            $table->enum('iva', ['Responsable Inscripto','Responsable No inscripto','No Inscripto','Monotributista','Consumidor Final'])->nullable();
            $table->enum('llego', ['Google','Diario','Recomendado','Radio','Ya compró','Página Web','Ya conocía','Mercado Libre','Otro'])->nullable();
            $table->text('foto')->nullable();
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
        Schema::dropIfExists('clientes');
    }
}
