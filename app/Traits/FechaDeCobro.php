<?php

namespace App\Traits;

use App\Models\Entidad;

/**
 * Fecha de un pago.
 *
 * Lo que entra a la caja física (entidad tangible) lleva SIEMPRE la fecha del
 * momento: el movimiento impacta en la caja abierta del día, así que una fecha
 * cargada para atrás o para adelante descuadraría el arqueo sin que nadie se dé
 * cuenta. Por eso no se toma lo que venga del formulario.
 *
 * El resto —cheque, transferencia, crédito— sí lleva fecha propia: un cheque a
 * treinta días tiene que poder cargarse con su fecha real.
 */
trait FechaDeCobro
{
    /**
     * @param Entidad|null $entidad     Forma de pago del cobro
     * @param mixed        $fechaCargada Lo que mandó el formulario
     */
    protected function fechaDeCobro($entidad, $fechaCargada): string
    {
        if ($entidad && $entidad->tangible) {
            return now()->toDateString();
        }

        $fecha = trim((string) $fechaCargada);

        return $fecha !== '' ? $fecha : now()->toDateString();
    }
}
