<?php

namespace App\Traits;

use App\Models\MovimientoCaja;
use App\Models\MovimientoCuenta;

/**
 * Al editar una venta / venta de pieza / servicio, los controladores vuelven a
 * crear los movimientos de caja y de cuenta. Sin dar de baja los viejos, la
 * plata quedaba duplicada.
 *
 * Regla acordada con el negocio: nunca se altera una caja ya cerrada. Si algún
 * movimiento de la operación cayó en una caja cerrada, la edición se rechaza y
 * el ajuste se hace a mano desde caja.
 */
trait RehaceMovimientos
{
    /**
     * Devuelve un mensaje de error si algún movimiento de la operación quedó en
     * una caja ya cerrada, o null si se puede rehacer todo sin problemas.
     *
     * @param string $columna 'venta_id' | 'venta_pieza_id' | 'servicio_id'
     */
    protected function movimientosBloqueadosPorCajaCerrada(string $columna, $operacionId): ?string
    {
        if (!$operacionId) {
            return null;
        }

        $movimiento = MovimientoCaja::with('caja.sucursal')
            ->where($columna, $operacionId)
            ->get()
            ->first(function ($mov) {
                return !$mov->caja || $mov->caja->estado === 'Cerrada';
            });

        if (!$movimiento) {
            return null;
        }

        $caja = $movimiento->caja;

        if (!$caja) {
            return 'No se puede modificar: hay un movimiento de caja de esta operación que ya no tiene caja asociada. Hacé el ajuste a mano desde caja.';
        }

        $sucursal = optional($caja->sucursal)->nombre ?: 'sucursal ' . $caja->sucursal_id;
        $fecha    = $caja->fecha ? $caja->fecha->format('d/m/Y') : 's/f';

        return "No se puede modificar: el cobro de esta operación ya impactó en la caja de $sucursal del $fecha, que está cerrada. "
            . 'Hacé el ajuste a mano desde caja.';
    }

    /**
     * Da de baja los movimientos de caja y de cuenta de la operación, para que
     * el controlador los vuelva a crear con los importes nuevos.
     *
     * Llamar SIEMPRE después de movimientosBloqueadosPorCajaCerrada().
     *
     * @param string $columna 'venta_id' | 'venta_pieza_id' | 'servicio_id'
     */
    protected function bajaMovimientosOperacion(string $columna, $operacionId): void
    {
        if (!$operacionId) {
            return;
        }

        MovimientoCaja::where($columna, $operacionId)->delete();
        MovimientoCuenta::where($columna, $operacionId)->delete();
    }
}
