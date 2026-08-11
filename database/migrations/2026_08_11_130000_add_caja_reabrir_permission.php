<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permiso `caja-reabrir`: permite a un administrador reabrir una caja que ya
 * fue cerrada. Sirve para el caso en que un usuario cierra la caja del día por
 * error y, por la regla de una sola caja por sucursal y por día, no puede abrir
 * otra. El administrador la reabre para poder seguir operando.
 */
class AddCajaReabrirPermission extends Migration
{
    public function up()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'caja-reabrir',
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

        $permission = Permission::where('name', 'caja-reabrir')->first();
        if ($permission) {
            $permission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
