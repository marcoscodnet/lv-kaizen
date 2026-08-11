<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFechaAndUniqueToCajasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Regla de negocio: una sola caja por sucursal y por día.
     * Se agrega la columna `fecha` (día de la caja) y un índice único
     * (sucursal_id, fecha) que impide abrir más de una caja por sucursal
     * en el mismo día.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->date('fecha')->nullable()->after('sucursal_id');
        });

        // Backfill: para cajas existentes, usar la fecha de apertura como día.
        DB::statement('UPDATE cajas SET fecha = DATE(apertura) WHERE fecha IS NULL');

        Schema::table('cajas', function (Blueprint $table) {
            $table->unique(['sucursal_id', 'fecha'], 'cajas_sucursal_fecha_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropUnique('cajas_sucursal_fecha_unique');
            $table->dropColumn('fecha');
        });
    }
}
