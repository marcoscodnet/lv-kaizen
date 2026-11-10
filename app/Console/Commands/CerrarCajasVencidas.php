<?php

namespace App\Console\Commands;

use App\Models\Caja;
use Illuminate\Console\Command;

/**
 * Cierre automático de cajas al final del día.
 *
 * Regla: una caja por sucursal y por día. Si nadie la cerró a mano, se cierra
 * sola con el mismo cálculo que el cierre manual (Caja::cerrar()). Cierra toda
 * caja 'Abierta' cuya fecha sea anterior a hoy, así también recupera días en
 * que el scheduler no corrió.
 *
 * Programado en App\Console\Kernel a las 00:00. Si el cron no corre, las
 * pantallas de caja igual cierran las vencidas al entrar (Caja::cerrarVencidas).
 * A mano:
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
        $etiqueta = function (Caja $caja) {
            return "#{$caja->id} — " . optional($caja->sucursal)->nombre . ' — ' . $caja->fecha->format('d/m/Y');
        };

        if ($this->option('dry-run')) {
            $cajas = Caja::with('sucursal')->vencidas()->orderBy('fecha')->get();

            foreach ($cajas as $caja) {
                $this->line('Se cerraría la caja ' . $etiqueta($caja));
            }
            $this->warn("{$cajas->count()} caja(s). No se modificó nada (--dry-run).");

            return self::SUCCESS;
        }

        // Misma lógica que la red de seguridad de las pantallas de caja.
        $cajas = Caja::cerrarVencidas();

        if ($cajas->isEmpty()) {
            $this->info('No hay cajas abiertas de días anteriores. Nada que hacer.');
            return self::SUCCESS;
        }

        foreach ($cajas->load('sucursal') as $caja) {
            $this->info('Caja ' . $etiqueta($caja) . ' cerrada. Final: $' . number_format($caja->final, 2, ',', '.'));
        }

        return self::SUCCESS;
    }
}
