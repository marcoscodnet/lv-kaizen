<?php

namespace App\Traits;

/**
 * Una operación (venta, venta de artículos o servicio) que ya tiene la forma de
 * pago confirmada no se vuelve a modificar. Así nadie puede retocar los pagos
 * después de que auditoría los verificó. Solo quien tenga el permiso
 * `operacion-cobrada-editar` (el Administrador) puede entrar a editarla.
 */
trait BloqueaOperacionCobrada
{
    /**
     * Devuelve un redirect a la vista de la operación si está bloqueada, o null.
     * Llamarlo al principio de edit() y de update().
     */
    protected function bloqueadaPorCobro($operacion, string $rutaShow)
    {
        if ($operacion
            && !auth()->user()->can('operacion-cobrada-editar')
            && $operacion->pagos()->exists()) {
            return redirect()->route($rutaShow, $operacion->id)
                ->with('error', 'La operación ya tiene la forma de pago confirmada y no se puede modificar. Solo un administrador puede hacerlo.');
        }
        return null;
    }

    /** Columna `tiene_pagos` (1/0) para los listados, para ocultar el lápiz. */
    protected function tienePagosRaw(string $columnaPago, string $tabla): string
    {
        return "EXISTS (SELECT 1 FROM pagos WHERE pagos.{$columnaPago} = {$tabla}.id) as tiene_pagos";
    }
}
