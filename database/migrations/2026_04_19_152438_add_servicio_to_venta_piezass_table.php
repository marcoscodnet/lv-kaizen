<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServicioToVentaPiezassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('venta_piezas', function (Blueprint $table) {
            $table->unsignedBigInteger('servicio_id')->nullable()->after('pedido');
            $table->foreign('servicio_id')->references('id')->on('servicios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('venta_piezas', function (Blueprint $table) {
            $table->dropForeign(['servicio_id']);
            $table->dropColumn('servicio_id');
        });
    }
}
