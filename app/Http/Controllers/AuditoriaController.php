<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Autorizacion;
use App\Models\Entidad;
use App\Models\MovimientoCuenta;
use App\Models\Pago;
use App\Traits\SanitizesInput;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;


class AuditoriaController extends Controller
{
    use SanitizesInput;

    function __construct()
    {
        $this->middleware('permission:autorizacion-listar', ['only' => ['index', 'dataTable', 'datos']]);
        $this->middleware('permission:autorizacion-crear', ['only' => ['autorizar', 'autorizarLote']]);
        $this->middleware('permission:autorizacion-eliminar', ['only' => ['desautorizar']]);
    }

    public function index()
    {
        $entidads = Entidad::where('autorizacion', 1)
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->prepend('Todas', '-1');

        return view('auditoria.index', compact('entidads'));
    }

    public function dataTable(Request $request)
    {
        $columnas = [
            'pagos.id',
            'pagos.fecha',
            'origen',
            'clientes.nombre',
            'entidads.nombre',
            'pagos.monto',
            'pagos.pagado',
            'estado',
        ];

        $columnaOrden = $columnas[$request->input('order.0.column')] ?? 'pagos.fecha';
        $orden = $request->input('order.0.dir') ?? 'desc';
        $busqueda = $request->input('search.value');
        $estado = $request->input('estado');       // pendiente | autorizado | (vacio = todos)
        $entidadId = $request->input('entidad_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        // Origin label: which document the payment belongs to
        $origenSql = "CASE
            WHEN pagos.venta_id IS NOT NULL THEN CONCAT('Unidad #', pagos.venta_id)
            WHEN pagos.venta_pieza_id IS NOT NULL THEN CONCAT('Pieza #', pagos.venta_pieza_id)
            WHEN pagos.servicio_id IS NOT NULL THEN CONCAT('Servicio #', pagos.servicio_id)
            ELSE '—' END";

        $estadoSql = "CASE WHEN autorizacions.id IS NOT NULL THEN 'Autorizado' ELSE 'Pendiente' END";

        // Resolve client across the 3 possible origins
        $clienteSql = "COALESCE(cv.nombre, cvp.nombre, cs.nombre)";

        $query = Pago::query()
            ->select(
                'pagos.id as id',
                'pagos.fecha',
                'pagos.monto',
                'pagos.pagado',
                'pagos.contadora',
                'pagos.comprobante_path',
                'pagos.venta_id',
                'pagos.venta_pieza_id',
                'pagos.servicio_id',
                'entidads.nombre as entidad_nombre',
                'entidads.cuenta as entidad_cuenta',
                'entidads.tangible as entidad_tangible',
                DB::raw("$origenSql as origen"),
                DB::raw("$estadoSql as estado"),
                DB::raw("$clienteSql as cliente"),
                'autorizacions.id as autorizacion_id',
                'autorizacions.fecha as autorizado_fecha',
                DB::raw("IFNULL(users.name, autorizacions.user_name) as autorizado_por")
            )
            ->join('entidads', 'pagos.entidad_id', '=', 'entidads.id')
            ->leftJoin('autorizacions', 'autorizacions.pago_id', '=', 'pagos.id')
            // join clients via each origin
            ->leftJoin('ventas', 'pagos.venta_id', '=', 'ventas.id')
            ->leftJoin('clientes as cv', 'ventas.cliente_id', '=', 'cv.id')
            ->leftJoin('venta_piezas', 'pagos.venta_pieza_id', '=', 'venta_piezas.id')
            ->leftJoin('clientes as cvp', 'venta_piezas.cliente_id', '=', 'cvp.id')
            ->leftJoin('servicios', 'pagos.servicio_id', '=', 'servicios.id')
            ->leftJoin('clientes as cs', 'servicios.cliente_id', '=', 'cs.id')
            ->leftJoin('users', 'autorizacions.user_id', '=', 'users.id')
            // only payments from entities that require authorization
            ->where('entidads.autorizacion', 1);

        // Filter by state
        if ($estado === 'pendiente') {
            $query->whereNull('autorizacions.id');
        } elseif ($estado === 'autorizado') {
            $query->whereNotNull('autorizacions.id');
        }

        if (!empty($entidadId) && $entidadId != '-1') {
            $query->where('pagos.entidad_id', $entidadId);
        }

        if (!empty($fechaDesde)) {
            $query->whereDate('pagos.fecha', '>=', $fechaDesde);
        }
        if (!empty($fechaHasta)) {
            $query->whereDate('pagos.fecha', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda, $origenSql, $clienteSql) {
                $q->orWhere('entidads.nombre', 'like', "%$busqueda%")
                    ->orWhereRaw("$clienteSql like ?", ["%$busqueda%"])
                    ->orWhereRaw("$origenSql like ?", ["%$busqueda%"]);
            });
        }

        $recordsFiltered = $query->count();
        $recordsTotal = Pago::join('entidads', 'pagos.entidad_id', '=', 'entidads.id')
            ->where('entidads.autorizacion', 1)->count();

        $datos = $query->orderBy($columnaOrden, $orden)
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        return response()->json([
            'data' => $datos,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
        ]);
    }

    // Return single payment data for the authorize modal
    public function datos($pagoId)
    {
        $pago = Pago::with('entidad')->findOrFail($pagoId);

        return response()->json([
            'id'           => $pago->id,
            'monto'        => $pago->monto,
            'pagado'       => $pago->pagado,
            'contadora'    => $pago->contadora,
            'entidad'      => $pago->entidad->nombre ?? '',
            'comprobante'  => $pago->comprobante_path ? asset($pago->comprobante_path) : null,
        ]);
    }

    // Authorize a single payment
    public function autorizar(Request $request, $pagoId)
    {
        $request->validate([
            'pagado'    => 'required|numeric|min:0',
            'contadora' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $pago = Pago::with('entidad')->findOrFail($pagoId);

            // Prevent double authorization
            if (Autorizacion::where('pago_id', $pago->id)->exists()) {
                DB::rollBack();
                return response()->json(['error' => 'Este pago ya está autorizado.'], 422);
            }

            $acreditado = $this->sanitizeInput($request->pagado);

            // Create authorization record
            Autorizacion::create([
                'user_id'       => auth()->id(),
                'pago_id'       => $pago->id,
                'fecha'         => now(),
                'observaciones' => $this->sanitizeInput($request->observaciones ?? null),
            ]);

            // Set credited amount and accountant date on the payment
            $pago->pagado    = $acreditado;
            $pago->contadora = $request->filled('contadora') ? $request->contadora : null;
            $pago->save();

            // Adjust account movement if credited differs from original amount
            if ($pago->entidad && $pago->entidad->cuenta && (float)$acreditado != (float)$pago->monto) {
                $mov = MovimientoCuenta::where('pago_id', $pago->id)->first();
                if ($mov) {
                    $mov->monto = $acreditado;
                    $mov->save();
                }
            }

            DB::commit();
            return response()->json(['success' => 'Pago autorizado correctamente.']);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    // Authorize many payments at once (credited = original amount, no adjustment)
    public function autorizarLote(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['error' => 'No se seleccionaron pagos.'], 422);
        }

        DB::beginTransaction();
        try {
            $autorizados = 0;
            $pagos = Pago::whereIn('id', $ids)->get();

            foreach ($pagos as $pago) {
                if (Autorizacion::where('pago_id', $pago->id)->exists()) {
                    continue; // skip already authorized
                }

                Autorizacion::create([
                    'user_id'   => auth()->id(),
                    'pago_id'   => $pago->id,
                    'fecha'     => now(),
                ]);

                // Batch: credited equals the original amount, no movement adjustment needed
                $pago->pagado    = $pago->monto;
                $pago->contadora = now()->toDateString();
                $pago->save();

                $autorizados++;
            }

            DB::commit();
            return response()->json(['success' => "$autorizados pago(s) autorizado(s)."]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    // Undo authorization
    public function desautorizar($pagoId)
    {
        DB::beginTransaction();
        try {
            $pago = Pago::with('entidad')->findOrFail($pagoId);

            $autorizacion = Autorizacion::where('pago_id', $pago->id)->first();
            if (!$autorizacion) {
                DB::rollBack();
                return response()->json(['error' => 'Este pago no está autorizado.'], 422);
            }

            // Revert account movement to the original payment amount
            if ($pago->entidad && $pago->entidad->cuenta) {
                $mov = MovimientoCuenta::where('pago_id', $pago->id)->first();
                if ($mov) {
                    $mov->monto = $pago->monto;
                    $mov->save();
                }
            }

            // Clear credited fields and delete authorization
            $pago->pagado    = null;
            $pago->contadora = null;
            $pago->save();

            $autorizacion->delete();

            DB::commit();
            return response()->json(['success' => 'Pago desautorizado correctamente.']);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
