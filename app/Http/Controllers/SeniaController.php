<?php

namespace App\Http\Controllers;

use App\Models\Autorizacion;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Entidad;
use App\Models\MovimientoCaja;
use App\Models\MovimientoCuenta;
use App\Models\Pago;
use App\Models\Senia;
use App\Models\Sucursal;
use App\Traits\FechaDeCobro;
use App\Traits\SanitizesInput;
use App\Traits\ValidaCajaAbierta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Señas: alta (con una o varias formas de pago), listado, baja (devolver /
 * retener / anular) y las señas de un cliente para aplicarlas al cobrar.
 *
 * - Pago en efectivo (entidad tangible): ingreso en la caja del día, se acredita solo.
 * - Transferencia, tarjeta, etc. (entidad con autorización): va a Auditoría.
 *   Hasta que se autoriza, la seña queda "Pendiente de autorización" y no se
 *   puede aplicar.
 * - Entidad con cuenta: movimiento en la cuenta de la entidad, como en las ventas.
 *
 * La aplicación a la operación está en el trait AplicaSenias.
 */
class SeniaController extends Controller
{
    use SanitizesInput, FechaDeCobro, ValidaCajaAbierta;

    public const FILTRO_VIGENTES = 'Vigentes';
    public const FILTRO_TODAS    = 'Todas';

    public function __construct()
    {
        $this->middleware('permission:senia-listar', ['only' => ['index']]);
        $this->middleware('permission:senia-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:senia-baja', ['only' => ['devolver', 'retener', 'destroy']]);
    }

    /* ======================================================================
     * Listado
     * ====================================================================== */

    public function index(Request $request)
    {
        $user   = auth()->user();
        $estado = $request->input('estado', self::FILTRO_VIGENTES);

        $senias = Senia::with(['cliente', 'sucursal', 'pagos.entidad', 'pagos.autorizacion', 'movimientosCaja', 'devolucionEntidad'])
            ->visiblesPara($user)
            ->when(true, function ($q) use ($estado) {
                $this->filtrarPorEstado($q, $estado);
            })
            ->when($request->filled('sucursal_id') && $request->sucursal_id != '-1', function ($q) use ($request) {
                $q->where('sucursal_id', $request->sucursal_id);
            })
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $buscar = trim($request->buscar);
                $q->where(function ($w) use ($buscar) {
                    $w->whereHas('cliente', function ($c) use ($buscar) {
                        $c->where('nombre', 'like', "%$buscar%")->orWhere('documento', 'like', "%$buscar%");
                    })->orWhere('detalle', 'like', "%$buscar%");
                });
            })
            ->orderByDesc('fecha')
            ->paginate(50)
            ->withQueryString();

        $sucursals = Senia::veTodasLasSucursales($user)
            ? Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1')
            : Sucursal::where('id', $user->sucursal_id)->pluck('nombre', 'id');

        $estados = [
            self::FILTRO_VIGENTES,
            Senia::PENDIENTE_AUTORIZACION,
            Senia::DISPONIBLE,
            Senia::APLICADA,
            Senia::DEVUELTA,
            Senia::RETENIDA,
            self::FILTRO_TODAS,
        ];

        // Formas de pago para devolver (incluye efectivo, transferencia, etc.)
        $entidadsDevolucion = $this->entidadsActivas()->get();

        return view('senias.index', compact('senias', 'sucursals', 'estados', 'estado', 'entidadsDevolucion'));
    }

    /** "Pendiente de autorización": vigente con algún pago que requiere autorización y no la tiene. */
    private function filtrarPorEstado($q, string $estado): void
    {
        $sinAutorizar = function ($p) {
            $p->whereHas('entidad', function ($e) {
                $e->where('autorizacion', 1);
            })->whereDoesntHave('autorizacion');
        };

        switch ($estado) {
            case self::FILTRO_TODAS:
                break;
            case self::FILTRO_VIGENTES:
                $q->where('estado', Senia::VIGENTE);
                break;
            case Senia::PENDIENTE_AUTORIZACION:
                $q->where('estado', Senia::VIGENTE)->whereHas('pagos', $sinAutorizar);
                break;
            case Senia::DISPONIBLE:
                $q->where('estado', Senia::VIGENTE)->whereDoesntHave('pagos', $sinAutorizar);
                break;
            default:
                $q->where('estado', $estado);
        }
    }

    /* ======================================================================
     * Alta
     * ====================================================================== */

    public function create()
    {
        $user = auth()->user();

        $sucursals = Senia::veTodasLasSucursales($user)
            ? Sucursal::orderBy('nombre')->pluck('nombre', 'id')
            : Sucursal::where('id', $user->sucursal_id)->pluck('nombre', 'id');

        $entidads = $this->entidadsActivas()->get(['id', 'nombre', 'forma', 'autorizacion', 'tangible']);

        return view('senias.create', compact('sucursals', 'entidads'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Sin permiso para otras sucursales, la seña es de la sucursal del usuario
        if (!Senia::veTodasLasSucursales($user)) {
            $request->merge(['sucursal_id' => $user->sucursal_id]);
        }

        $entidadesValidas = $this->entidadsActivas()->pluck('id')->all();

        $validator = Validator::make($request->all(), [
            'cliente_id'     => 'required|exists:clientes,id',
            'sucursal_id'    => 'required|exists:sucursals,id',
            'concepto_senia' => 'nullable|string|max:255',
            'forma'          => 'required',
            'entidad_id'     => 'required|array|min:1',
            'entidad_id.*'   => 'required|in:' . implode(',', $entidadesValidas ?: [0]),
            'monto'          => 'required|array',
            'monto.*'        => 'required|numeric|min:0.01',
            'fecha_pago.*'   => 'required|date',
        ], [
            'cliente_id.required'  => 'Elegí el cliente que deja la seña.',
            'sucursal_id.required' => 'No se pudo determinar la sucursal de la seña.',
            'forma.required'       => 'Elegí la condición de pago.',
            'entidad_id.required'  => 'Cargá al menos una forma de pago.',
            'entidad_id.min'       => 'Cargá al menos una forma de pago.',
            'entidad_id.*.required' => 'Elegí la forma de pago.',
            'entidad_id.*.in'      => 'La forma de pago elegida no es válida.',
            'monto.*.required'     => 'El importe es obligatorio.',
            'monto.*.min'          => 'El importe tiene que ser mayor a cero.',
            'fecha_pago.*.required' => 'La fecha del pago es obligatoria.',
        ]);

        // El efectivo entra a la caja del día: tiene que estar abierta
        $validator->after(function ($validator) use ($request) {
            if ($mensaje = $this->faltaCajaAbierta($request->sucursal_id, (array) $request->input('entidad_id', []))) {
                $validator->errors()->add('caja', $mensaje);
            }
        });

        if ($validator->fails()) {
            $cliente = Cliente::find($request->input('cliente_id'));
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->all() + ['cliente_nombre' => optional($cliente)->nombre]);
        }

        DB::beginTransaction();
        try {
            $cliente = Cliente::findOrFail($request->cliente_id);

            $senia = Senia::create([
                'cliente_id'  => $cliente->id,
                'sucursal_id' => $request->sucursal_id,
                'fecha'       => now(),
                'monto'       => 0,
                'detalle'     => $this->sanitizeInput($request->concepto_senia),
                'estado'      => Senia::VIGENTE,
                'user_id'     => $user->id,
            ]);

            $concepto   = Senia::concepto();
            $referencia = 'Seña #' . $senia->id . ' — ' . $cliente->nombre
                . ($senia->detalle ? ' — ' . $senia->detalle : '');
            $total = 0;

            foreach ($request->entidad_id as $i => $entidadId) {
                $entidad = Entidad::find($entidadId);

                $pago = new Pago();
                $pago->senia_id    = $senia->id;
                $pago->entidad_id  = $entidadId;
                $pago->monto       = $this->sanitizeInput($request->monto[$i]);
                // Lo que entra a la caja lleva la fecha del momento, no la cargada
                $pago->fecha       = $this->fechaDeCobro($entidad, $this->sanitizeInput($request->fecha_pago[$i]));
                // Efectivo y similares se acreditan solos; el resto, en Auditoría
                if ($entidad && $entidad->acreditaAutomatico()) {
                    $pago->pagado    = $pago->monto;
                    $pago->contadora = null;
                }
                $pago->detalle     = $this->sanitizeInput($request->detalle[$i] ?? null);
                $pago->observacion = $this->sanitizeInput($request->observaciones[$i] ?? null);
                $pago->save();

                $total += (float) $pago->monto;

                $this->guardarComprobantesPago($pago, $request, $i);

                if ($entidad && $entidad->tangible) {
                    $caja = Caja::abiertaDelDia($senia->sucursal_id);
                    if (!$caja) {
                        throw new \Exception('No hay caja abierta para esta sucursal. No se puede registrar el pago en efectivo.');
                    }
                    MovimientoCaja::create([
                        'caja_id'     => $caja->id,
                        'concepto_id' => $concepto->id,
                        'entidad_id'  => $entidad->id,
                        'senia_id'    => $senia->id,
                        'tipo'        => 'Ingreso',
                        'monto'       => $pago->monto,
                        'acreditado'  => 1,
                        'fecha'       => now(),
                        'referencia'  => $referencia,
                    ]);
                }

                if ($entidad && $entidad->cuenta) {
                    MovimientoCuenta::create([
                        'entidad_id' => $entidad->id,
                        'tipo'       => 'Ingreso',
                        'monto'      => $pago->monto,
                        'fecha'      => $pago->fecha,
                        'concepto'   => $concepto->nombre,
                        'pago_id'    => $pago->id,
                        'senia_id'   => $senia->id,
                        'user_id'    => $user->id,
                    ]);
                }
            }

            $senia->update(['monto' => $total]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $ex->getMessage()])->withInput();
        }

        $senia->load('pagos.entidad', 'pagos.autorizacion');
        $mensaje = $senia->estaAutorizada()
            ? 'Seña #' . $senia->id . ' registrada. Ya está disponible para aplicar.'
            : 'Seña #' . $senia->id . ' registrada. Queda pendiente de autorización en Auditoría: hasta que la autoricen no se puede aplicar.';

        return redirect()->route('senias.index')->with('success', $mensaje);
    }

    /* ======================================================================
     * Bajas
     * ====================================================================== */

    /**
     * Devolver: el cliente desiste y se le devuelve la plata, con la forma de
     * pago que se elija. Efectivo sale de la caja abierta de hoy; una forma con
     * cuenta, como egreso de esa cuenta; cualquier otra solo queda registrada.
     */
    public function devolver(Request $request, $id)
    {
        $senia = $this->seniaVigente($id);
        if (!$senia instanceof Senia) {
            return $senia;
        }

        if (!$senia->estaAutorizada()) {
            return redirect()->back()->withErrors('La seña #' . $senia->id . ' está pendiente de autorización: no se puede devolver hasta que Auditoría confirme lo que entró. Si no se va a usar, podés retenerla o anularla.');
        }

        $entidadesValidas = $this->entidadsActivas()->pluck('id')->all();
        $acreditado = $senia->acreditado();

        $validator = Validator::make($request->all(), [
            'entidad_id' => 'required|in:' . implode(',', $entidadesValidas ?: [0]),
            'monto'      => 'required|numeric|min:0.01|max:' . $acreditado,
        ], [
            'entidad_id.required' => 'Elegí con qué forma de pago se devuelve.',
            'entidad_id.in'       => 'La forma de pago elegida no es válida.',
            'monto.required'      => 'Indicá el importe a devolver.',
            'monto.max'           => 'No se puede devolver más de lo acreditado ($' . number_format($acreditado, 2, ',', '.') . ').',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $entidad = Entidad::findOrFail($request->entidad_id);
        $monto   = round((float) $this->sanitizeInput($request->monto), 2);

        $caja = null;
        if ($entidad->tangible) {
            $sucursalId = auth()->user()->sucursal_id ?: $senia->sucursal_id;
            $caja = Caja::abiertaDelDia($sucursalId);
            if (!$caja) {
                return redirect()->back()->withErrors('Para devolver en efectivo tiene que haber una caja abierta hoy en tu sucursal.');
            }
        }

        DB::transaction(function () use ($senia, $entidad, $monto, $caja, $request) {
            $referencia = 'Devolución seña #' . $senia->id . ' — ' . optional($senia->cliente)->nombre;

            if ($caja) {
                MovimientoCaja::create([
                    'caja_id'     => $caja->id,
                    'concepto_id' => Senia::conceptoDevolucion()->id,
                    'entidad_id'  => $entidad->id,
                    'senia_id'    => $senia->id,
                    'tipo'        => 'Egreso',
                    'monto'       => $monto,
                    'acreditado'  => 1,
                    'fecha'       => now(),
                    'referencia'  => $referencia,
                ]);
            }

            if ($entidad->cuenta) {
                MovimientoCuenta::create([
                    'entidad_id'  => $entidad->id,
                    'tipo'        => 'Egreso',
                    'monto'       => $monto,
                    'fecha'       => now(),
                    'concepto'    => Senia::CONCEPTO_DEVOLUCION,
                    'senia_id'    => $senia->id,
                    'user_id'     => auth()->id(),
                    'observacion' => $referencia,
                ]);
            }

            $senia->update([
                'estado'                => Senia::DEVUELTA,
                'devolucion_entidad_id' => $entidad->id,
                'devolucion_monto'      => $monto,
                'fecha_baja'            => now(),
                'observacion'           => $this->observacion($senia, $request),
            ]);
        });

        $donde = $caja ? 'Se registró el egreso en la caja #' . $caja->id . '.'
            : ($entidad->cuenta ? 'Se registró el egreso en la cuenta de ' . $entidad->nombre . '.' : 'Quedó registrada con ' . $entidad->nombre . '.');

        return redirect()->back()->with('success', 'Seña #' . $senia->id . ' devuelta. ' . $donde);
    }

    /** Retener: el cliente desiste y la seña no se devuelve. No mueve caja ni cuenta. */
    public function retener(Request $request, $id)
    {
        $senia = $this->seniaVigente($id);
        if (!$senia instanceof Senia) {
            return $senia;
        }

        $senia->update([
            'estado'      => Senia::RETENIDA,
            'fecha_baja'  => now(),
            'observacion' => $this->observacion($senia, $request),
        ]);

        return redirect()->back()->with('success', 'Seña #' . $senia->id . ' retenida.');
    }

    /**
     * Anular una seña cargada por error. Solo si sigue vigente, ningún pago fue
     * autorizado todavía y el efectivo está en una caja que sigue abierta.
     */
    public function destroy($id)
    {
        $senia = $this->seniaVigente($id);
        if (!$senia instanceof Senia) {
            return $senia;
        }

        $autorizados = $senia->pagos->filter(function (Pago $p) {
            return $p->autorizacion !== null;
        });
        if ($autorizados->isNotEmpty()) {
            return redirect()->back()->withErrors('La seña #' . $senia->id . ' ya tiene pagos autorizados en Auditoría: no se puede anular. Pedí que la desautoricen o devolvela.');
        }

        $cajaCerrada = $senia->movimientosCaja->first(function (MovimientoCaja $m) {
            return optional($m->caja)->estado !== 'Abierta';
        });
        if ($cajaCerrada) {
            return redirect()->back()->withErrors('El efectivo de la seña #' . $senia->id . ' entró en la caja #' . $cajaCerrada->caja_id . ', que ya está cerrada: no se puede anular. Devolvela o retenela.');
        }

        DB::transaction(function () use ($senia) {
            MovimientoCaja::where('senia_id', $senia->id)->delete();
            MovimientoCuenta::where('senia_id', $senia->id)->delete();
            $pagoIds = $senia->pagos->pluck('id');
            Comprobante::whereIn('pago_id', $pagoIds)->delete();
            Pago::whereIn('id', $pagoIds)->delete();
            $senia->delete();
        });

        return redirect()->back()->with('success', 'Seña #' . $senia->id . ' anulada.');
    }

    /* ======================================================================
     * Señas de un cliente para aplicar al cobrar (JSON)
     * ====================================================================== */

    public function aplicables(Request $request)
    {
        if (!$request->filled('cliente_id') || !ctype_digit((string) $request->cliente_id)) {
            return response()->json([]);
        }

        $columna = in_array($request->columna, Senia::DESTINOS, true) ? $request->columna : 'venta_id';
        $opId    = ctype_digit((string) $request->operacion_id) ? (int) $request->operacion_id : null;

        $senias = Senia::delClienteParaCobrar((int) $request->cliente_id, $columna, $opId, auth()->user())
            ->with('pagoAplicado')
            ->get()
            ->map(function (Senia $s) use ($columna, $opId) {
                $aplicada = $opId && $s->estado === Senia::APLICADA && (int) $s->{$columna} === $opId;
                $monto = $aplicada && $s->pagoAplicado ? (float) $s->pagoAplicado->monto : $s->acreditado();

                return [
                    'id'         => $s->id,
                    'fecha'      => $s->fecha->format('d/m/Y'),
                    'monto'      => $monto,               // lo que cubre: lo acreditado
                    'cobrado'    => (float) $s->monto,
                    'sucursal'   => optional($s->sucursal)->nombre,
                    'formas'     => $s->pagos->map(function ($p) {
                        return optional($p->entidad)->nombre;
                    })->filter()->unique()->implode(', '),
                    'detalle'    => $s->detalle,
                    'aplicada'   => $aplicada,
                    'disponible' => $s->estaDisponible(),
                ];
            });

        return response()->json($senias);
    }

    /* ======================================================================
     * Auxiliares
     * ====================================================================== */

    /** Formas de pago que se pueden usar (todas las activas menos la interna "Seña"). */
    private function entidadsActivas()
    {
        return Entidad::where('activa', 1)->where('nombre', '!=', Senia::ENTIDAD)->orderBy('nombre');
    }

    private function seniaVigente($id)
    {
        $senia = Senia::with('pagos.entidad', 'pagos.autorizacion', 'movimientosCaja.caja', 'cliente')->findOrFail($id);

        if (!$senia->esVisiblePara(auth()->user())) {
            abort(403, 'No tenés acceso a las señas de otra sucursal.');
        }

        if (!$senia->esVigente()) {
            return redirect()->back()->withErrors('La seña #' . $senia->id . ' ya está ' . strtolower($senia->estado) . '.');
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

    // Comprobantes adjuntos de una fila de pago (mismo criterio que ventas)
    private function guardarComprobantesPago(Pago $pago, Request $request, int $i): void
    {
        $uid = $request->input("pago_uid.$i");
        if ($uid === null) {
            return;
        }

        $files = $request->file("comprobantes_$uid");
        if (empty($files)) {
            return;
        }
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['jpeg', 'jpg', 'png', 'pdf'])) {
                continue;
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                continue;
            }

            $dir = 'files/comprobantes/' . date('Y') . '/' . date('m');
            if (!file_exists(public_path($dir))) {
                mkdir(public_path($dir), 0775, true);
            }
            $filename = uniqid('comp_') . '.' . $ext;
            $file->move(public_path($dir), $filename);

            Comprobante::create([
                'pago_id' => $pago->id,
                'path'    => "$dir/$filename",
            ]);
        }
    }
}
