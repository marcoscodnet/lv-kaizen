<?php

namespace App\Traits;

use App\Models\Pieza;
use App\Models\StockPieza;
use Carbon\Carbon;

/**
 * Movimiento de existencias de los artículos vendidos.
 *
 * Los artículos de tipos sin stock (patentamiento, seguro y demás conceptos que
 * se cobran pero no se tienen en depósito) se saltean en los tres pasos: no se
 * les pide stock, no se les descuenta y no se les repone.
 *
 * Nota: VentaPiezaController tiene su propia copia de estos bucles, heredada de
 * antes. Conviene unificarla acá cuando se toque esa pantalla.
 */
trait StockArticulos
{
    /**
     * @param array $filas cada una con pieza_id, sucursal_id y cantidad
     * @throws \Exception si falta stock de algún artículo que sí lleva existencias
     */
    protected function validarStockArticulos(array $filas): void
    {
        $sinStock = Pieza::idsSinStock();

        foreach ($filas as $fila) {
            if (in_array($fila['pieza_id'], $sinStock)) {
                continue;
            }

            $disponible = StockPieza::where('pieza_id', $fila['pieza_id'])
                ->where('sucursal_id', $fila['sucursal_id'])
                ->sum('cantidad');

            if ($disponible < $fila['cantidad']) {
                $articulo = Pieza::find($fila['pieza_id']);
                $nombre = $articulo
                    ? trim($articulo->codigo . ' - ' . $articulo->descripcion)
                    : "#{$fila['pieza_id']}";

                throw new \Exception("No hay suficiente stock de $nombre en la sucursal seleccionada.");
            }
        }
    }

    /** Descuenta las existencias de una fila, de los lotes más viejos primero. */
    protected function descontarStockArticulo(array $fila): void
    {
        if (in_array($fila['pieza_id'], Pieza::idsSinStock())) {
            return;
        }

        $lotes = StockPieza::where('pieza_id', $fila['pieza_id'])
            ->where('sucursal_id', $fila['sucursal_id'])
            ->orderBy('id')
            ->get();

        $restante = $fila['cantidad'];

        foreach ($lotes as $lote) {
            if ($lote->cantidad >= $restante) {
                $lote->cantidad -= $restante;
                $restante = 0;
            } else {
                $restante -= $lote->cantidad;
                $lote->cantidad = 0;
            }
            $lote->save();

            if ($restante <= 0) {
                break;
            }
        }
    }

    /**
     * Devuelve al stock lo que se había descontado, para cuando se edita o se
     * anula la operación.
     *
     * @param iterable $detalle filas PiezaVentaPieza (con su pieza cargada)
     */
    protected function reponerStockArticulos($detalle): void
    {
        foreach ($detalle as $pvp) {
            if ($pvp->cantidad <= 0 || !optional($pvp->pieza)->manejaStock()) {
                continue;
            }

            $stock = StockPieza::where('pieza_id', $pvp->pieza_id)
                ->where('sucursal_id', $pvp->sucursal_id)
                ->first();

            if ($stock) {
                $stock->cantidad += $pvp->cantidad;
                $stock->save();
                continue;
            }

            StockPieza::create([
                'pieza_id'      => $pvp->pieza_id,
                'sucursal_id'   => $pvp->sucursal_id,
                'cantidad'      => $pvp->cantidad,
                'remito'        => 'venta anulada',
                'ingreso'       => Carbon::now()->toDateString(),
                'costo'         => optional($pvp->pieza)->costo ?? 0,
                'precio_minimo' => optional($pvp->pieza)->precio_minimo ?? 0,
                'proveedor'     => null,
            ]);
        }
    }
}
