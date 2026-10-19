<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permiso `operacion-cobrada-editar`: una venta, venta de artículos u orden de
 * servicio que ya tiene la forma de pago confirmada queda bloqueada para todos
 * (ver App\Traits\BloqueaOperacionCobrada). Solo quien tenga este permiso puede
 * volver a editarla.
 */
class AddOperacionCobradaEditarPermission extends Migration
{
    public function up()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'operacion-cobrada-editar',
            'guard_name' => 'web',
        ]);

        $admin = Role::where('name', 'Administrador')->first();
        if ($admin) {
            $admin->givePermissionTo($permission);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::where('name', 'operacion-cobrada-editar')->first();
        if ($permission) {
            $permission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
