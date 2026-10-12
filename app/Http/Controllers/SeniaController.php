<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Senia;
use App\Models\Sucursal;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Señas de motos: listado, baja (devolver / retener) y las pendientes de un
 * cliente para aplicarlas en la venta. El alta se hace desde la caja
 * (MovimientoCajaController, concepto "Seña"); la aplicación, en VentaController.
 */
class SeniaController extends Controller
{
    use SanitizesInput;

    public function __construct()
    {
        $this->middleware('permission:caja-listar', ['only' => ['index']]);
        $this->middleware('permission:caja-movimiento-registrar', ['only' => ['devolver', 'retener']]);
    }

    public function index(Request $request)
    {
        $user   = auth()->user();
        $estado = $request->input('estado', Senia::PENDIENTE);

        $senias = Senia::with(['cliente', 'sucursal', 'movimientoCaja.entidad', 'venta'])
            ->visiblesPara($user)
            ->when($estado !== 'Todas', function ($q) use ($estado) {
                $q->where('estado', $estado);
            })
            ->when($request->filled('sucursal_id') && $request->sucursal_id != '-1', function ($q) use ($request) {
                $q->where('sucursal_id', $request->sucursal_id);
            })
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $buscar = trim($request->buscar);
                $q->whereHas('cliente', function ($c) use ($buscar) {
                    $c->where('nombre', 'like', "%$buscar%")->orWhere('documento', 'like', "%$buscar%");
                });
            })
            ->orderByDesc('fecha')
            ->paginate(50)
            ->withQueryString();

        $sucursals = Senia::veTodasLasSucursales($user)
            ? Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1')
            : Sucursal::where('id', $user->sucursal_id)->pluck('nombre', 'id');

        $estados = ['Todas', Senia::PENDIENTE, Senia::APLICADA, Senia::DEVUELTA, Senia::RETENIDA];

        return view('senias.index', compact('senias', 'sucursals', 'estados', 'estado'));
    }

    /**
     * Devolver: el cliente desiste y se le devuelve la plata. Sale como egreso
     * de la caja abierta de hoy (de la sucursal del usuario, o la de la seña
     * si el usuario no tiene sucursal), con la misma forma de pago.
     */
    public function devolver(Request $request, $id)
    {
        $senia = $this->seniaPendiente($id);
        if (!$senia instanceof Senia) {
            return $senia;
        }

        $sucursalId = auth()->user()->sucursal_id ?: $senia->sucursal_id;
        $caja = Caja::abiertaDelDia($sucursalId);
        if (!$caja) {
            return redirect()->back()->withErrors('Para devolver una seña tiene que haber una caja abierta hoy en tu sucursal.');
        }

        DB::transaction(function () use ($senia, $caja, $request) {
            $egreso = MovimientoCaja::create([
                'caja_id'     => $caja->id,
                'concepto_id' => Senia::conceptoDevolucion()->id,
                'entidad_id'  => optional($senia->movimientoCaja)->entidad_id,
                'tipo'        => 'Egreso',
                'monto'       => $senia->monto,
                'acreditado'  => 1,
                'fecha'       => now(),
                'referencia'  => 'Devolución seña #' . $senia->id . ' — ' . optional($senia->cliente)->nombre,
            ]);

            $senia->update([
                'estado'                   => Senia::DEVUELTA,
                'movimiento_devolucion_id' => $egreso->id,
                'fecha_baja'               => now(),
                'observacion'              => $this->observacion($senia, $request),
            ]);
        });

        return redirect()->back()->with('success', 'Seña devuelta: se registró el egreso en la caja #' . $caja->id . '.');
    }

    /** Retener: el cliente desiste y la seña no se devuelve. No mueve caja. */
    public function retener(Request $request, $id)
    {
        $senia = $this->seniaPendiente($id);
        if (!$senia instanceof Senia) {
            return $senia;
        }

        $senia->update([
            'estado'      => Senia::RETENIDA,
            'fecha_baja'  => now(),
            'observacion' => $this->observacion($senia, $request),
        ]);

        return redirect()->back()->with('success', 'Seña retenida.');
    }

    /** JSON: señas aplicables a la venta de un cliente (pendientes + ya aplicadas a esa venta). */
    public function aplicables(Request $request)
    {
        if (!$request->filled('cliente_id') || !ctype_digit((string) $request->cliente_id)) {
            return response()->json([]);
        }

        $ventaId = ctype_digit((string) $request->venta_id) ? (int) $request->venta_id : null;

        $senias = Senia::aplicablesA((int) $request->cliente_id, $ventaId, auth()->user())
            ->get()
            ->map(function (Senia $s) use ($ventaId) {
                return [
                    'id'        => $s->id,
                    'fecha'     => $s->fecha->format('d/m/Y'),
                    'monto'     => (float) $s->monto,
                    'sucursal'  => optional($s->sucursal)->nombre,
                    'caja_id'   => optional($s->movimientoCaja)->caja_id,
                    'detalle'   => $s->observacion,
                    'aplicada'  => $ventaId && (int) $s->venta_id === $ventaId,
                ];
            });

        return response()->json($senias);
    }

    private function seniaPendiente($id)
    {
        $senia = Senia::with('movimientoCaja', 'cliente')->findOrFail($id);

        if (!$senia->esVisiblePara(auth()->user())) {
            abort(403, 'No tenés acceso a las señas de otra sucursal.');
        }

        if (!$senia->esPendiente()) {
            return redirect()->back()->withErrors('La seña ya está ' . strtolower($senia->estado) . '.');
        }

        return $senia;
    }

    private function observacion(Senia $senia, Request $request): ?string
    {
        $nueva = trim((string) $this->sanitizeInput($request->input('observacion')));
        if ($nueva === '') {
            return $senia->observacion;
        }
        return trim(($senia->observacion ? $senia->observacion . ' | ' : '') . $nueva);
    }
}
