<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hay artículos que se cobran pero no se tienen en stock: patentamiento, seguro
 * y demás conceptos que se suman al momento de vender la moto.
 *
 * La tilde va en el TIPO y no en el artículo, así se define una vez ("Varios")
 * y todo lo que se cargue adentro se comporta igual, sin tocar código.
 *
 * Los tipos que ya existen (Repuestos, Accesorios, Indumentaria, Lubricantes)
 * quedan manejando stock, que es como venían funcionando.
 */
class AddManejaStockToTipoPiezasTable extends Migration
{
    public function up()
    {
        Schema::table('tipo_piezas', function (Blueprint $table) {
            $table->boolean('maneja_stock')->default(true)->after('nombre');
        });
    }

    public function down()
    {
        Schema::table('tipo_piezas', function (Blueprint $table) {
            $table->dropColumn('maneja_stock');
        });
    }
}
