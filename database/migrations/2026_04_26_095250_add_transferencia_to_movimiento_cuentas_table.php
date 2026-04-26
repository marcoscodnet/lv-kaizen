<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransferenciaToMovimientoCuentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimiento_cuentas', function (Blueprint $table) {
            $table->unsignedBigInteger('transferencia_id')->nullable()->after('pago_id');
            $table->foreign('transferencia_id')
                ->references('id')
                ->on('movimiento_cuentas')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movimiento_cuentas', function (Blueprint $table) {
            //
        });
    }
}
