<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposToMovimientoPiezasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimiento_piezas', function (Blueprint $table) {
            $table->string('estado')->default('Pendiente');
            $table->timestamp('aceptado')->nullable();
            $table->foreignId('user_acepta_id')->nullable()->constrained('users');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movimiento_piezas', function (Blueprint $table) {
            //
        });
    }
}
