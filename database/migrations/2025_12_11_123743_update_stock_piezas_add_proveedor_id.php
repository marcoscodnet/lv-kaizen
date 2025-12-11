<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStockPiezasAddProveedorId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_piezas', function (Blueprint $table) {
            // Agregar nueva FK proveedor_id
            $table->unsignedBigInteger('proveedor_id')->nullable()->after('precio_minimo');

            // Si ya existe la columna proveedor (texto), la eliminamos
            $table->dropColumn('proveedor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
