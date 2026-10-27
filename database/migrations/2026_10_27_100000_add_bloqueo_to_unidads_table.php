<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bloqueo de una unidad para la venta.
 *
 * Una moto puede quedar bloqueada por cualquier motivo (señada, sin
 * certificado, no está en condiciones de entrega...). Sigue visible en todos
 * los listados, con el motivo a la vista, pero no se puede vender.
 *
 * Bloquea cualquier usuario; desbloquea el que la bloqueó o un Administrador.
 */
class AddBloqueoToUnidadsTable extends Migration
{
    public function up()
    {
        Schema::table('unidads', function (Blueprint $table) {
            $table->boolean('bloqueada')->default(false)->after('observaciones');
            $table->string('motivo_bloqueo')->nullable()->after('bloqueada');
            $table->unsignedBigInteger('bloqueo_user_id')->nullable()->after('motivo_bloqueo');
            $table->dateTime('bloqueo_fecha')->nullable()->after('bloqueo_user_id');

            $table->foreign('bloqueo_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('unidads', function (Blueprint $table) {
            $table->dropForeign(['bloqueo_user_id']);
            $table->dropColumn(['bloqueada', 'motivo_bloqueo', 'bloqueo_user_id', 'bloqueo_fecha']);
        });
    }
}
