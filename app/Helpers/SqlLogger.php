<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SqlLogger
{
    protected static $log = [];

    public static function startLogging()
    {
        DB::listen(function ($query) {
            $sql = vsprintf(str_replace("?", "'%s'", $query->sql), $query->bindings);
            self::$log[] = $sql . ';';
        });
    }

    public static function saveLogToFile(string $filename)
    {
        $path = storage_path("logs/sql_seeders/$filename.sql");
        File::ensureDirectoryExists(dirname($path));
        File::put($path, implode("\n", self::$log));
        self::$log = []; // limpiar para la próxima
    }
}

