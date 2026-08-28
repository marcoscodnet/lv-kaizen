<?php

namespace App\Traits;

use App\Models\Pieza;
use App\Models\StockPieza;
use App\Models\Sucursal;

/**
 * Arma el listado de artículos que se puede elegir en una pantalla de carga.
 *
 * Sale de dos lados que hay que unir:
 *  - los que llevan existencias, desde el stock por sucursal;
 *  - los que no (patentamiento, seguro y demás conceptos), desde el catálogo,
 *    ofrecidos en todas las sucursales activas.
 *
 * Los dos vienen con la misma forma, así el JS de las pantallas no necesita
 * distinguirlos.
 */
trait CatalogoArticulos
{
    /**
     * Artículos elegibles, agrupados por id.
     *
     * @param bool $soloDisponibles true en las pantallas de carga (solo lo que
     *                              tiene stock), false al editar o consultar,
     *                              para que un artículo agotado siga apareciendo.
     */
    protected function catalogoArticulos(bool $soloDisponibles = true)
    {
        $user = auth()->user();
        $esAdministrador = $user && $user->hasRole('Administrador');

        $query = StockPieza::with(['pieza', 'sucursal']);

        if ($soloDisponibles) {
            $query->where('cantidad', '>', 0);
        }

        // Los no administradores solo ven el stock de su sucursal
        if (!$esAdministrador && $user) {
            $query->where('sucursal_id', $user->sucursal_id);
        }

        $conStock = $query->get()
            ->filter(function ($sp) {
                return $sp->pieza && $sp->sucursal;
            })
            ->map(function ($sp) {
                return [
                    'id'              => $sp->pieza_id,
                    'codigo'          => $sp->pieza->codigo,
                    'descripcion'     => $sp->pieza->descripcion,
                    'sucursal_id'     => $sp->sucursal_id,
                    'sucursal_nombre' => $sp->sucursal->nombre,
                    'costo'           => $sp->pieza->costo,
                    'precio_minimo'   => $sp->pieza->precio_minimo,
                ];
            })
            ->unique(function ($item) {
                return $item['id'] . '-' . $item['sucursal_id'];
            })
            ->values();

        return $conStock->concat($this->articulosSinStock())->groupBy('id');
    }

    /**
     * Artículos de tipos que no llevan existencias, con la misma forma que una
     * fila de stock: una por sucursal activa. Sin esto no aparecerían nunca en
     * el selector, porque el selector se arma desde el stock.
     */
    protected function articulosSinStock(): \Illuminate\Support\Collection
    {
        $articulos = Pieza::with('tipoPieza')
            ->whereHas('tipoPieza', function ($q) {
                $q->where('maneja_stock', 0);
            })
            ->get();

        if ($articulos->isEmpty()) {
            return collect();
        }

        $user = auth()->user();
        $sucursalesQuery = Sucursal::where('activa', 1);

        if ($user && !$user->hasRole('Administrador')) {
            $sucursalesQuery->where('id', $user->sucursal_id);
        }

        $sucursales = $sucursalesQuery->orderBy('nombre')->get(['id', 'nombre']);

        $filas = collect();
        foreach ($articulos as $articulo) {
            foreach ($sucursales as $sucursal) {
                $filas->push([
                    'id'              => $articulo->id,
                    'codigo'          => $articulo->codigo,
                    'descripcion'     => $articulo->descripcion,
                    'sucursal_id'     => $sucursal->id,
                    'sucursal_nombre' => $sucursal->nombre,
                    'costo'           => $articulo->costo,
                    'precio_minimo'   => $articulo->precio_minimo,
                ]);
            }
        }

        return $filas;
    }
}
