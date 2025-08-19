<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id')->nullable();
            $table->foreign('venta_id')->references('id')->on('ventas');
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->foreign('entidad_id')->references('id')->on('entidads');
            $table->decimal('monto', 10, 2)->nullable();
            $table->datetime('fecha')->nullable();
            $table->decimal('pagado', 10, 2)->nullable();
            $table->datetime('contadora')->nullable();
            $table->text('detalle')->nullable();
            $table->text('observacion')->nullable();
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
        Schema::dropIfExists('pagos');
    }
}
