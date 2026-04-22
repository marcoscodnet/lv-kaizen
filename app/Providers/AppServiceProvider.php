<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Models\MovimientoPieza;
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
        Schema::defaultStringLength(255);

        DB::listen(function ($query) {
            $sql = ltrim($query->sql);

            if (preg_match('/^(insert|update|delete|replace|truncate)/i', $sql)) {
                Log::debug(
                    "DB WRITE: {$query->sql} [" . implode(',', $query->bindings) . "]"
                );
            }
        });

        if (App::environment('local') && app()->runningInConsole() && $this->isMigrating()) {
            $this->logMigrationQueries();
        }

        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();

                if (!$user->hasRole('Administrador') && $user->sucursal_id) {
                    $pendientes = MovimientoPieza::where('estado', 'Pendiente')
                        ->where('sucursal_destino_id', $user->sucursal_id)
                        ->count();
                } else {
                    $pendientes = 0;
                }

                $view->with('alertaPendientesPiezas', $pendientes);
            }
        });
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
