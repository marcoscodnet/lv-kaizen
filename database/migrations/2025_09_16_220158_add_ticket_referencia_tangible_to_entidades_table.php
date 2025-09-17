<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTicketReferenciaTangibleToEntidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entidads', function (Blueprint $table) {
            $table->boolean('ticket')->default(false);
            $table->boolean('referencia')->default(false);
            $table->boolean('tangible')->default(false);
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
