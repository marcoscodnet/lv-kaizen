<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los conceptos que se cobran junto con la moto —patentamiento, seguro, casco—
 * se cargan en la misma pantalla de la venta, pero por dentro se guardan como
 * una venta de artículos colgada de esa venta.
 *
 * Así se reusa todo lo que ya existe (catálogo, precios, descuento de stock)
 * sin duplicar nada. Esa venta de artículos NO tiene pagos propios: la plata
 * vive toda en la venta de la moto, y por eso hay un solo importe a cobrar y
 * una sola autorización.
 *
 * ON DELETE CASCADE: si se anula la venta, se va con ella.
 */
class AddVentaToVentaPiezasTable extends Migration
{
    public function up()
    {
        Schema::table('venta_piezas', function (Blueprint $table) {
            $table->unsignedBigInteger('venta_id')->nullable()->after('servicio_id');
            $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('venta_piezas', function (Blueprint $table) {
            $table->dropForeign(['venta_id']);
            $table->dropColumn('venta_id');
        });
    }
}
