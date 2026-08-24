<?php

namespace App\Traits;

use App\Models\Caja;
use App\Models\Entidad;

/**
 * Chequeo de caja abierta ANTES de tocar la base.
 *
 * Antes el control estaba en medio del guardado: la operación se creaba, se
 * hacía rollback y el usuario volvía al formulario en blanco. Ahora falla como
 * error de validación, así que el aviso sale con el resto de los mensajes y la
 * carga queda en pantalla.
 *
 * Solo hace falta caja abierta si hay algún pago en efectivo (entidad tangible):
 * una venta financiada o por transferencia no toca la caja física.
 */
trait ValidaCajaAbierta
{
    /**
     * Devuelve el mensaje de error si falta la caja, o null si está todo bien.
     *
     * @param mixed $sucursalId  Sucursal cuya caja del día tiene que estar abierta
     * @param array $entidadIds  Entidades de los pagos que se están registrando
     */
    protected function faltaCajaAbierta($sucursalId, array $entidadIds = []): ?string
    {
        $entidadIds = collect($entidadIds)->filter()->unique()->all();

        if (empty($entidadIds)) {
            return null;
        }

        $hayEfectivo = Entidad::whereIn('id', $entidadIds)->where('tangible', 1)->exists();

        if (!$hayEfectivo) {
            return null;
        }

        if (!$sucursalId) {
            return 'No se pudo determinar la sucursal del pago en efectivo. Seleccioná la sucursal y volvé a confirmar.';
        }

        if (Caja::abiertaDelDia($sucursalId)) {
            return null;
        }

        return 'No hay caja abierta para esta sucursal. Abrí la caja del día y volvé a confirmar: los datos que cargaste quedan en pantalla.';
    }
}
