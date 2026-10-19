<?php

namespace App\Traits;

use App\Models\Pago;
use App\Models\Senia;
use Illuminate\Http\Request;

/**
 * Aplicar señas como pago de una operación: venta de moto (venta_id), venta de
 * artículos (venta_pieza_id) o servicio (servicio_id).
 *
 * Solo se aplican señas DISPONIBLES (vigentes y con todo lo que requiere
 * autorización ya autorizado) y valen lo ACREDITADO. Al aplicarla se crea un
 * pago con la forma de pago "Seña" (no tangible, sin cuenta, sin autorización):
 * cuenta como cobrado y acreditado, pero no genera movimiento de caja ni de
 * cuenta, porque la plata ya entró el día de la seña.
 *
 * Esos pagos llevan pagos.senia_aplicada_id y NO forman parte de las filas de
 * cobro del formulario: el formulario manda los ids tildados en senias[] y se
 * sincronizan aparte, después de rehacer los pagos comunes.
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

    /**
     * Señas tildadas que efectivamente se pueden aplicar: disponibles, o ya
     * aplicadas a esta misma operación (al editar).
     */
    protected function seniasAplicables(Request $request, string $columna, $id = null)
    {
        $ids = $this->idsSeniasDelRequest($request);
        if (!$ids || !$request->filled('cliente_id')) {
            return collect();
        }

        return Senia::delClienteParaCobrar($request->cliente_id, $columna, $id, auth()->user())
            ->whereIn('senias.id', $ids)
            ->get()
            ->filter(function (Senia $s) use ($columna, $id) {
                return $s->estaDisponible()
                    || ($id && $s->estado === Senia::APLICADA && (int) $s->{$columna} === (int) $id);
            })
            ->values();
    }

    /** Importe que cubren las señas tildadas (lo acreditado de cada una). */
    protected function montoSenias(Request $request, string $columna, $id = null): float
    {
        return (float) $this->seniasAplicables($request, $columna, $id)->sum(function (Senia $s) {
            return $this->importeDeSenia($s);
        });
    }

    /** Error de validación si alguna seña tildada no se puede aplicar. */
    protected function validarSenias($validator, Request $request, string $columna, $id = null): void
    {
        $ids = $this->idsSeniasDelRequest($request);
        if (!$ids) {
            return;
        }

        $validas = $this->seniasAplicables($request, $columna, $id)->pluck('id')->all();
        $invalidas = array_diff($ids, $validas);

        if ($invalidas) {
            $validator->errors()->add('senias', 'No se puede aplicar la seña #' . implode(', #', $invalidas)
                . ': no es de este cliente, está pendiente de autorización, ya fue usada o dada de baja, o es de otra sucursal.');
        }
    }

    /**
     * Deja aplicadas a la operación exactamente las señas tildadas: libera las
     * que se destildaron y aplica las nuevas. Llamar dentro de la transacción,
     * después de guardar los pagos comunes.
     */
    protected function sincronizarSenias(Request $request, string $columna, $id): void
    {
        $pedidas  = $this->seniasAplicables($request, $columna, $id)->keyBy('id');
        $actuales = Senia::where($columna, $id)->where('estado', Senia::APLICADA)->get()->keyBy('id');

        foreach ($actuales as $seniaId => $senia) {
            if (!$pedidas->has($seniaId)) {
                $this->liberarSenia($senia);
            }
        }

        $entidad = null;
        foreach ($pedidas as $seniaId => $senia) {
            if ($actuales->has($seniaId) && Pago::where('senia_aplicada_id', $seniaId)->where($columna, $id)->exists()) {
                continue;
            }

            $entidad = $entidad ?: Senia::entidad();
            $importe = $this->importeDeSenia($senia);

            $pago = Pago::create([
                $columna            => $id,
                'entidad_id'        => $entidad->id,
                'monto'             => $importe,
                'fecha'             => $senia->fecha,
                'pagado'            => $importe, // ya entró (y se auditó) con la seña
                'contadora'         => null,
                'detalle'           => 'Seña #' . $senia->id . ' del ' . $senia->fecha->format('d/m/Y')
                    . ($senia->detalle ? ' — ' . $senia->detalle : ''),
                'senia_aplicada_id' => $senia->id,
            ]);

            $senia->update([
                'estado'           => Senia::APLICADA,
                $columna           => $id,
                'pago_aplicado_id' => $pago->id,
            ]);
        }
    }

    /** Al borrar la operación, sus señas vuelven a quedar vigentes. */
    protected function liberarSeniasDe(string $columna, $id): void
    {
        Senia::where($columna, $id)->where('estado', Senia::APLICADA)->get()
            ->each(function (Senia $senia) {
                $this->liberarSenia($senia);
            });
    }

    /** Una seña aplicada vale lo que dice su pago; una disponible, lo acreditado. */
    private function importeDeSenia(Senia $senia): float
    {
        if ($senia->estado === Senia::APLICADA && $senia->pago_aplicado_id) {
            $pago = Pago::find($senia->pago_aplicado_id);
            if ($pago) {
                return (float) $pago->monto;
            }
        }
        return $senia->acreditado();
    }

    private function liberarSenia(Senia $senia): void
    {
        Pago::where('senia_aplicada_id', $senia->id)->delete();

        $senia->update([
            'estado'           => Senia::VIGENTE,
            'venta_id'         => null,
            'venta_pieza_id'   => null,
            'servicio_id'      => null,
            'pago_aplicado_id' => null,
        ]);
    }
}
