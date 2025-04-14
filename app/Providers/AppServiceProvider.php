<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(255); // Ajusta la longitud máxima de las cadenas
        if (App::environment('local') && app()->runningInConsole() && $this->isMigrating()) {
            $this->logMigrationQueries();
        }
    }

    protected function isMigrating()
    {
        $argv = implode(' ', $_SERVER['argv'] ?? []);
        return str_contains($argv, 'migrate');
    }

    protected function logMigrationQueries()
    {
        // Detectar migración en curso (último archivo de migración que se está ejecutando)
        $migrationFile = $this->getCurrentMigrationFilename();

        if (!$migrationFile) return;

        $logPath = database_path("sql/migraciones/{$migrationFile}.sql");

        // Crear carpeta si no existe
        File::ensureDirectoryExists(database_path('sql/migraciones'));

        // Registrar queries
        DB::listen(function ($query) use ($logPath) {
            $sql = $query->sql;

            foreach ($query->bindings as $binding) {
                $binding = is_numeric($binding) ? $binding : "'".addslashes($binding)."'";
                $sql = preg_replace('/\?/', $binding, $sql, 1);
            }

            File::append($logPath, $sql . ";\n");
        });
    }

    protected function getCurrentMigrationFilename()
    {
        // Detectar si se está corriendo una migración específica
        $args = $_SERVER['argv'] ?? [];

        // Ver si se están ejecutando migraciones específicas
        foreach ($args as $arg) {
            if (str_ends_with($arg, '.php') && str_contains($arg, 'migrations')) {
                return basename($arg, '.php');
            }
        }

        // Si no, devolver timestamp aproximado (última migración en ejecución)
        $files = glob(database_path('migrations/*.php'));
        rsort($files); // Últimas primero

        return basename($files[0] ?? '', '.php');
    }
}
