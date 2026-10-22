<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permiso `operacion-ver-todas`: cada vendedor ve y edita solo sus ventas y
 * ventas de artículos (ver User::puedeVerOperacion()). El Administrador las ve
 * todas por rol; este permiso sirve para dárselo a otro rol si hace falta
 * (por ejemplo un encargado de sucursal).
 */
class AddOperacionVerTodasPermission extends Migration
{
    public function up()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'operacion-ver-todas',
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

        $permission = Permission::where('name', 'operacion-ver-todas')->first();
        if ($permission) {
            $permission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
