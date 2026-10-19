<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permiso `servicio-reabrir`: una orden de servicio cerrada (pagado = 1) queda
 * bloqueada para todos, aunque tengan `servicio-editar` (los técnicos lo tienen
 * para cargar la orden mientras está abierta). Solo quien tenga este permiso
 * puede entrar a editarla o destildar "Cerrado".
 */
class AddServicioReabrirPermission extends Migration
{
    public function up()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'servicio-reabrir',
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

        $permission = Permission::where('name', 'servicio-reabrir')->first();
        if ($permission) {
            $permission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
