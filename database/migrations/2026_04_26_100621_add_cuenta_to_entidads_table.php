<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCuentaToEntidadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entidads', function (Blueprint $table) {
            Schema::table('entidades', function (Blueprint $table) {
                $table->boolean('cuenta')->default(false)->after('activa');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entidads', function (Blueprint $table) {
            //
        });
    }
}
