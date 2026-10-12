<?php

namespace App\Traits;

use App\Models\Pago;
use App\Models\Senia;
use App\Models\Venta;
use Illuminate\Http\Request;

/**
 * Aplicar señas como pago de una venta.
 *
 * La seña ya entró a la caja el día que se pagó, así que al aplicarla se crea un
 * pago con la forma de pago "Seña" (no tangible, sin cuenta, sin autorización):
 * cuenta como cobrado y acreditado, pero no genera movimiento en la caja de hoy.
 *
 * Esos pagos llevan pagos.senia_id y NO forman parte de las filas de cobro del
 * formulario: el formulario manda los ids tildados en senias[] y se sincronizan
 * aparte, antes o después de rehacer los pagos comunes.
 */
trait AplicaSenias
{
    /** Ids de seña que vienen del formulario. */
    protected function idsSeniasDelRequest(Request $request): array
    {
        return collect((array) $request->input('senias', []))
            ->filter(function ($id) {
                return ctype_digit((string) $id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();
    }

    /** Señas tildadas que efectivamente se pueden aplicar a esta venta. */
    protected function seniasAplicables(Request $request, $ventaId = null)
    {
        $ids = $this->idsSeniasDelRequest($request);
        if (!$ids || !$request->filled('cliente_id')) {
            return collect();
        }

        return Senia::aplicablesA($request->cliente_id, $ventaId, auth()->user())
            ->whereIn('senias.id', $ids)
            ->get();
    }

    protected function montoSenias(Request $request, $ventaId = null): float
    {
        return (float) $this->seniasAplicables($request, $ventaId)->sum('monto');
    }

    /** Error de validación si alguna seña tildada no se puede aplicar. */
    protected function validarSenias($validator, Request $request, $ventaId = null): void
    {
        $ids = $this->idsSeniasDelRequest($request);
        if (!$ids) {
            return;
        }

        $validas = $this->seniasAplicables($request, $ventaId)->pluck('id')->all();
        $invalidas = array_diff($ids, $validas);

        if ($invalidas) {
            $validator->errors()->add('senias', 'No se puede aplicar la seña #' . implode(', #', $invalidas)
                . ': no es de este cliente, ya fue usada o dada de baja, o es de otra sucursal.');
        }
    }

    /**
     * Deja aplicadas a la venta exactamente las señas tildadas: libera las que
     * se destildaron y aplica las nuevas. Llamar dentro de la transacción.
     */
    protected function sincronizarSenias(Request $request, Venta $venta): void
    {
        $pedidas = $this->seniasAplicables($request, $venta->id)->keyBy('id');
        $actuales = Senia::where('venta_id', $venta->id)->where('estado', Senia::APLICADA)->get()->keyBy('id');

        foreach ($actuales as $id => $senia) {
            if (!$pedidas->has($id)) {
                $this->liberarSenia($senia);
            }
        }

        $entidad = null;
        foreach ($pedidas as $id => $senia) {
            if ($actuales->has($id) && Pago::where('senia_id', $id)->where('venta_id', $venta->id)->exists()) {
                continue;
            }

            $entidad = $entidad ?: Senia::entidad();

            $pago = Pago::create([
                'venta_id'    => $venta->id,
                'entidad_id'  => $entidad->id,
                'monto'       => $senia->monto,
                'fecha'       => $senia->fecha,
                'pagado'      => $senia->monto, // ya entró a la caja el día de la seña
                'contadora'   => null,
                'detalle'     => 'Seña #' . $senia->id . ' del ' . $senia->fecha->format('d/m/Y')
                    . ' (caja #' . optional($senia->movimientoCaja)->caja_id . ')',
                'senia_id'    => $senia->id,
            ]);

            $senia->update([
                'estado'   => Senia::APLICADA,
                'venta_id' => $venta->id,
                'pago_id'  => $pago->id,
            ]);
        }
    }

    /** Al borrar la venta, sus señas vuelven a quedar pendientes. */
    protected function liberarSeniasDeVenta(Venta $venta): void
    {
        Senia::where('venta_id', $venta->id)->where('estado', Senia::APLICADA)->get()
            ->each(function (Senia $senia) {
                $this->liberarSenia($senia);
            });
    }

    private function liberarSenia(Senia $senia): void
    {
        Pago::where('senia_id', $senia->id)->delete();

        $senia->update([
            'estado'   => Senia::PENDIENTE,
            'venta_id' => null,
            'pago_id'  => null,
        ]);
    }
}
