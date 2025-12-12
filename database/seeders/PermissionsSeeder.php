<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Helpers\SqlLogger;
class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SqlLogger::startLogging();

        $permissions = [
            'pieza-movimiento-listar',
            'pieza-movimiento-crear',
            'pieza-movimiento-editar',
            'pieza-movimiento-eliminar',
            'pieza-movimiento-ver',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        SqlLogger::saveLogToFile('PermissionsSeeder'); // Guardar a archivo
    }
}
