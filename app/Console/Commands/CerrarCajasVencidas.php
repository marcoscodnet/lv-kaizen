<?php

namespace App\Console\Commands;

use App\Models\Caja;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cierre automático de cajas al final del día.
 *
 * Regla: una caja por sucursal y por día. Si nadie la cerró a mano, se cierra
 * sola con el mismo cálculo que el cierre manual (Caja::cerrar()). Cierra toda
 * caja 'Abierta' cuya fecha sea anterior a hoy, así también recupera días en
 * que el scheduler no corrió.
 *
 * Programado en App\Console\Kernel a las 00:00. A mano:
 *
 *   php artisan cajas:cerrar-vencidas --dry-run
 *   php artisan cajas:cerrar-vencidas
 */
class CerrarCajasVencidas extends Command
{
    protected $signature = 'cajas:cerrar-vencidas
                            {--dry-run : Solo lista las cajas que se cerrarían, sin tocar nada}';

    protected $description = 'Cierra automáticamente las cajas que quedaron abiertas de días anteriores';

    public function handle(): int
    {
        $cajas = Caja::with('sucursal')
            ->where('estado', 'Abierta')
            ->whereDate('fecha', '<', now()->toDateString())
            ->orderBy('fecha')
            ->get();

        if ($cajas->isEmpty()) {
            $this->info('No hay cajas abiertas de días anteriores. Nada que hacer.');
            return self::SUCCESS;
        }

        foreach ($cajas as $caja) {
            $etiqueta = "#{$caja->id} — " . optional($caja->sucursal)->nombre . ' — ' . $caja->fecha->format('d/m/Y');

            if ($this->option('dry-run')) {
                $this->line("Se cerraría la caja $etiqueta");
                continue;
            }

            $caja->cerrar();
            $this->info("Caja $etiqueta cerrada. Final: $" . number_format($caja->final, 2, ',', '.'));
            Log::info("Cierre automático de caja $etiqueta", ['final' => $caja->final]);
        }

        if ($this->option('dry-run')) {
            $this->warn("{$cajas->count()} caja(s). No se modificó nada (--dry-run).");
        }

        return self::SUCCESS;
    }
}
