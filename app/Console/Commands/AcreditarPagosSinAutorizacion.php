<?php

namespace App\Console\Commands;

use App\Models\Pago;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfill de pagos históricos.
 *
 * Auditoría solo lista pagos cuya entidad tiene autorizacion = 1. Los pagos de
 * entidades sin autorización (efectivo y similares) quedaron con pagado en NULL
 * para siempre, porque no había dónde acreditarlos a mano. Este comando los
 * acredita por el importe cobrado.
 *
 * No se ejecuta solo: hay que correrlo a mano.
 *
 *   php artisan pagos:acreditar-sin-autorizacion --dry-run
 *   php artisan pagos:acreditar-sin-autorizacion
 */
class AcreditarPagosSinAutorizacion extends Command
{
    protected $signature = 'pagos:acreditar-sin-autorizacion
                            {--dry-run : Solo informa cuántos pagos se acreditarían, sin tocar nada}';

    protected $description = 'Acredita (pagado = monto) los pagos ya cargados cuya entidad no requiere autorización';

    public function handle(): int
    {
        $query = Pago::whereNull('pagado')
            ->whereHas('entidad', function ($q) {
                $q->where('autorizacion', 0);
            });

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No hay pagos pendientes de acreditar. Nada que hacer.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("Se acreditarían $total pago(s). No se modificó nada (--dry-run).");
            return self::SUCCESS;
        }

        $actualizados = 0;

        DB::transaction(function () use ($query, &$actualizados) {
            $query->with('entidad')->chunkById(200, function ($pagos) use (&$actualizados) {
                foreach ($pagos as $pago) {
                    $pago->pagado = $pago->monto;
                    $pago->contadora = null;
                    $pago->save();
                    $actualizados++;
                }
            });
        });

        $this->info("Listo: $actualizados pago(s) acreditado(s).");

        return self::SUCCESS;
    }
}
