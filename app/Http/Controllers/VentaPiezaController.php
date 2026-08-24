<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Concepto;
use App\Models\Entidad;
use App\Models\MovimientoCaja;
use App\Models\Provincia;
use App\Models\StockPieza;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\VentaPieza;
use App\Models\PiezaVentaPieza;
use App\Models\Pago;
use App\Http\Controllers\Controller;
use App\Traits\RehaceMovimientos;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VentaPiezaController extends Controller
{
    use SanitizesInput, RehaceMovimientos;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:venta-pieza-listar|venta-pieza-crear|venta-pieza-editar|venta-pieza-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:venta-pieza-crear', ['only' => ['create','store']]);
        $this->middleware('permission:venta-pieza-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:venta-pieza-eliminar', ['only' => ['destroy']]);
    }


    // SQL CASE that returns 'Autorizada' when the sale has payments and all of them are authorized
    private function autorizacionCase(string $alias = ''): string
    {
        $as = $alias ? " as $alias" : '';
        return "CASE
            WHEN EXISTS (SELECT 1 FROM pagos WHERE pagos.venta_pieza_id = venta_piezas.id)
             AND NOT EXISTS (
                 SELECT 1 FROM pagos p2
                 JOIN entidads e2 ON e2.id = p2.entidad_id
                 LEFT JOIN autorizacions a2 ON a2.pago_id = p2.id
                 WHERE p2.venta_pieza_id = venta_piezas.id
                   AND e2.autorizacion = 1
                   AND a2.id IS NULL
             )
            THEN 'Autorizada' ELSE 'No autorizada' END{$as}";
    }

    // SQL predicate (for servicios queries) that is TRUE when the service order is NOT authorized.
    // A service order is "Autorizada" when it has payments and all payments requiring authorization
    // already have their autorizacions row. Parts can only be assigned while it is still "No autorizada".
    private function servicioNoAutorizadaRaw(): string
    {
        return "NOT (
            EXISTS (SELECT 1 FROM pagos WHERE pagos.servicio_id = servicios.id)
            AND NOT EXISTS (
                SELECT 1 FROM pagos p2
                JOIN entidads e2 ON e2.id = p2.entidad_id
                LEFT JOIN autorizacions a2 ON a2.pago_id = p2.id
                WHERE p2.servicio_id = servicios.id
                  AND e2.autorizacion = 1
                  AND a2.id IS NULL
            )
        )";
    }

    // Save uploaded proof file under public/files/comprobantes/{year}/{month}
    private function guardarComprobante($file): string
    {
        $year = date('Y');
        $month = date('m');
        $dir = "files/comprobantes/$year/$month";
        $fullDir = public_path($dir);

        if (!file_exists($fullDir)) {
            mkdir($fullDir, 0775, true);
        }

        $filename = uniqid('comp_') . '.' . $file->getClientOriginalExtension();
        $file->move($fullDir, $filename);

        return "$dir/$filename";
    }

    // Save every proof file uploaded for a payment row (supports one or many files)
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
            \App\Models\Comprobante::create([
                'pago_id' => $pago->id,
                'path'    => $this->guardarComprobante($file),
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $ventaPiezas = VentaPieza::all();
        $users = \App\Models\User::orderBy('name')
            ->pluck('name', 'id')
            ->prepend('Todos', '-1');
        return view ('ventaPiezas.index',compact('ventaPiezas','users'));
    }


    public function dataTable(Request $request)
    {
        $columnas = [
            'venta_piezas.fecha',
            DB::raw("IFNULL(clientes.nombre, IFNULL(clientes_serv.nombre, venta_piezas.cliente))"),
            DB::raw("CASE WHEN venta_piezas.servicio_id IS NOT NULL THEN NULLIF(TRIM(CONCAT_WS(' ', (SELECT m.nombre FROM marcas m WHERE m.id = servicios.marca_id), IFNULL((SELECT mo.nombre FROM modelos mo WHERE mo.id = servicios.modelo_id), servicios.modelo), NULLIF(servicios.motor, ''))), '') ELSE NULL END"),
            'venta_piezas.destino',
            DB::raw("(
            SELECT SUM(pvp.precio * pvp.cantidad)
            FROM pieza_venta_piezas pvp
            WHERE pvp.venta_pieza_id = venta_piezas.id
        ) as precio_total"),
            DB::raw("IFNULL((
            SELECT GROUP_CONCAT(DISTINCT s.nombre SEPARATOR ', ')
            FROM pieza_venta_piezas pvp
            INNER JOIN sucursals s ON s.id = pvp.sucursal_id
            WHERE pvp.venta_pieza_id = venta_piezas.id
        ), IFNULL(suc_serv.nombre, sucursals.nombre))"),
            DB::raw("IFNULL(users.name, venta_piezas.user_name)"),
            DB::raw("(
            SELECT GROUP_CONCAT(p.codigo SEPARATOR ', ')
            FROM pieza_venta_piezas pvp
            INNER JOIN piezas p ON p.id = pvp.pieza_id
            WHERE pvp.venta_pieza_id = venta_piezas.id
        ) as piezas_codigos"),
            DB::raw($this->autorizacionCase()),
        ];

        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');
        $user_id = $request->input('user_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $query = VentaPieza::select(
            'venta_piezas.id as id',
            'venta_piezas.fecha',
            DB::raw("IFNULL(clientes.nombre, IFNULL(clientes_serv.nombre, venta_piezas.cliente)) as cliente"),
            DB::raw("CASE WHEN venta_piezas.servicio_id IS NOT NULL THEN NULLIF(TRIM(CONCAT_WS(' ', (SELECT m.nombre FROM marcas m WHERE m.id = servicios.marca_id), IFNULL((SELECT mo.nombre FROM modelos mo WHERE mo.id = servicios.modelo_id), servicios.modelo), NULLIF(servicios.motor, ''))), '') ELSE NULL END as orden_servicio"),
            'venta_piezas.destino',
            DB::raw("(
            SELECT SUM(pvp.precio * pvp.cantidad)
            FROM pieza_venta_piezas pvp
            WHERE pvp.venta_pieza_id = venta_piezas.id
        ) as precio_total"),
            DB::raw("IFNULL((
            SELECT GROUP_CONCAT(DISTINCT s.nombre SEPARATOR ', ')
            FROM pieza_venta_piezas pvp
            INNER JOIN sucursals s ON s.id = pvp.sucursal_id
            WHERE pvp.venta_pieza_id = venta_piezas.id
        ), IFNULL(suc_serv.nombre, sucursals.nombre)) as sucursal_nombre"),
            DB::raw("IFNULL(users.name, venta_piezas.user_name) as usuario_nombre"),
            DB::raw($this->autorizacionCase('autorizacion')),
            DB::raw("(
            SELECT GROUP_CONCAT(p.codigo SEPARATOR ', ')
            FROM pieza_venta_piezas pvp
            INNER JOIN piezas p ON p.id = pvp.pieza_id
            WHERE pvp.venta_pieza_id = venta_piezas.id
        ) as piezas_codigos"),

        )
            ->leftJoin('sucursals', 'venta_piezas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('users', 'venta_piezas.user_id', '=', 'users.id')
            ->leftJoin('clientes', 'venta_piezas.cliente_id', '=', 'clientes.id')
            ->leftJoin('servicios', 'venta_piezas.servicio_id', '=', 'servicios.id')
            ->leftJoin('clientes as clientes_serv', 'servicios.cliente_id', '=', 'clientes_serv.id')
            ->leftJoin('sucursals as suc_serv', 'servicios.sucursal_id', '=', 'suc_serv.id');

        if (!empty($user_id) && $user_id != '-1') {
            $query->where('venta_piezas.user_id', $user_id);
        }

        if (!empty($fechaDesde)) {
            $query->whereDate('venta_piezas.fecha', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('venta_piezas.fecha', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($query) use ($columnas, $busqueda) {
                foreach ($columnas as $columna) {
                    if ($columna) {
                        $query->orWhere($columna, 'like', "%$busqueda%");
                    }
                }
            });
        }

        $recordsFiltered = $query->count();

        $datos = $query->orderBy($columnaOrden, $orden)
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        $recordsTotal = VentaPieza::count();

        return response()->json([
            'data' => $datos,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $user = auth()->user();
        $esAdministrador = $user->hasRole('Administrador');

        $stockPiezasQuery = StockPieza::with(['pieza', 'sucursal'])
            ->where('cantidad', '>', 0); // Only show pieces with available stock

        // Non-admin users see only stock from their own branch
        if (!$esAdministrador) {
            $stockPiezasQuery->where('sucursal_id', $user->sucursal_id);
        }
        $stockPiezas = $stockPiezasQuery
            ->get()
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

        $stockPiezasJson = $stockPiezas->groupBy('id');

        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        // Load selectable service orders for the Taller destination dropdown:
        // only orders that are still OPEN (not closed / pagado = 0) AND NOT authorized.
        $serviciosAbiertos = \App\Models\Servicio::where('pagado', 0)
            ->whereRaw($this->servicioNoAutorizadaRaw())
            ->orderBy('id', 'desc')
            ->get(['id', 'modelo', 'motor', 'chasis']);

        $entidads = \App\Models\Entidad::orderBy('nombre')->where('activa', 1)->get(['id', 'nombre', 'forma', 'autorizacion']);
        return view('ventaPiezas.create', compact('users', 'stockPiezasJson', 'sucursals', 'provincias', 'serviciosAbiertos', 'entidads'));
    }

    /**
     * Crea la venta de piezas, o actualiza una existente si se pasa $ventaExistente.
     *
     * Ojo: al editar NO se crea una venta nueva. La venta conserva su id porque
     * los movimientos de caja y de cuenta la referencian; si se borrara y se
     * volviera a crear, esos movimientos quedarían huérfanos y duplicados.
     *
     * $preservado viene de update() e indexa, por posición del pago, lo que el
     * vendedor no puede tocar: acreditado, fecha de contadora, comprobantes ya
     * subidos y la autorización del auditor.
     */
    private function guardarVenta(Request $request, ?VentaPieza $ventaExistente = null, array $preservado = []): VentaPieza
    {
        $input = $this->sanitizeInput($request->all());

        // Validate stock before saving
        foreach ($request->pieza_id as $i => $piezaId) {
            $sucursalId = $request->sucursal_id_item[$i];
            $cantidadSolicitada = $request->cantidad[$i];

            $stockDisponible = StockPieza::where('pieza_id', $piezaId)
                ->where('sucursal_id', $sucursalId)
                ->sum('cantidad');

            if ($stockDisponible < $cantidadSolicitada) {
                throw new \Exception("No hay suficiente stock de la pieza {$piezaId} en la sucursal seleccionada.");
            }
        }

        // Guard: parts can only be assigned to a service order that is still OPEN and NOT authorized.
        // Re-check server-side so a stale/forged servicio_id cannot bypass the filtered dropdown.
        if (($input['destino'] ?? null) === 'Taller' && !empty($input['servicio_id'])) {
            $asignable = \App\Models\Servicio::where('id', $input['servicio_id'])
                ->where('pagado', 0)
                ->whereRaw($this->servicioNoAutorizadaRaw())
                ->exists();

            if (!$asignable) {
                throw new \Exception("No se pueden asignar repuestos a la orden de servicio #{$input['servicio_id']}: está cerrada o autorizada.");
            }
        }

        // Save main sale (keeps its id when editing)
        $venta = $ventaExistente ?: new VentaPieza();
        $venta->user_id    = $input['user_id'];
        $venta->fecha      = $input['fecha'];
        $venta->destino    = $input['destino'];
        $venta->cliente_id = $input['cliente_id'] ?? null;
        $venta->sucursal_id = $input['sucursal_id'] ?? null;
        $venta->pedido     = $input['pedido'] ?? null;
        $venta->servicio_id = ($input['destino'] === 'Taller') ? ($input['servicio_id'] ?? null) : null;
        $venta->forma      = ($input['destino'] === 'Salón') ? ($input['forma'] ?? null) : null;
        $venta->save();

        // Save details and discount stock
        foreach ($request->pieza_id as $i => $piezaId) {
            $detalle = new PiezaVentaPieza();
            $detalle->venta_pieza_id = $venta->id;
            $detalle->pieza_id       = $piezaId;
            $detalle->sucursal_id    = $request->sucursal_id_item[$i];
            $detalle->cantidad       = $request->cantidad[$i];
            $detalle->precio         = $request->precio[$i];
            $detalle->save();

            $stockPiezas = StockPieza::where('pieza_id', $piezaId)
                ->where('sucursal_id', $request->sucursal_id_item[$i])
                ->orderBy('id')
                ->get();

            $cantidadRestante = $request->cantidad[$i];

            foreach ($stockPiezas as $stockPieza) {
                if ($stockPieza->cantidad >= $cantidadRestante) {
                    $stockPieza->cantidad -= $cantidadRestante;
                    $cantidadRestante = 0;
                } else {
                    $cantidadRestante -= $stockPieza->cantidad;
                    $stockPieza->cantidad = 0;
                }
                $stockPieza->save();
                if ($cantidadRestante <= 0) {
                    break;
                }
            }
        }

        // Save payments only for Salón
        if ($input['destino'] === 'Salón' && $request->filled('entidad_id')) {

            $conceptoVenta = Concepto::firstOrCreate(['nombre' => 'Venta de pieza']);

            // On Salón sales there is no top-level sucursal_id (it stays null),
            // so resolve the branch from the sold items, falling back to the seller's branch.
            $sucursalCaja = $request->sucursal_id
                ?: ($request->input('sucursal_id_item.0')
                    ?: optional(\App\Models\User::find($request->user_id))->sucursal_id);

            foreach ($request->entidad_id as $i => $entidadId) {
                $entidad = Entidad::find($entidadId);
                $viejo   = $preservado[$i] ?? null;

                $pago = new Pago();
                $pago->venta_pieza_id = $venta->id;
                $pago->entidad_id     = $entidadId;
                $pago->monto          = $this->sanitizeInput($request->monto[$i]);
                $pago->fecha          = $this->sanitizeInput($request->fecha_pago[$i]);
                // Entidades sin autorización (efectivo y similares) no pasan por
                // auditoría: se acreditan solas por el importe cobrado y sin fecha
                // de contadora. El resto lo completa el auditor.
                if ($entidad && $entidad->acreditaAutomatico()) {
                    $pago->pagado    = $pago->monto;
                    $pago->contadora = null;
                } elseif ($viejo) {
                    // Datos del auditor: el vendedor no los toca al editar
                    $pago->pagado    = $viejo['pagado'];
                    $pago->contadora = $viejo['contadora'];
                }
                $pago->detalle        = $this->sanitizeInput($request->detalle[$i] ?? null);
                $pago->observacion    = $this->sanitizeInput($request->observaciones[$i] ?? null);

                $pago->save();

                // Re-link proofs and authorization that already existed for this payment row
                foreach (($viejo['comprobantes'] ?? []) as $pathViejo) {
                    \App\Models\Comprobante::create([
                        'pago_id' => $pago->id,
                        'path'    => $pathViejo,
                    ]);
                }

                if (!empty($viejo['autorizacion'])) {
                    \App\Models\Autorizacion::create([
                        'user_id'   => $viejo['autorizacion']['user_id'],
                        'user_name' => $viejo['autorizacion']['user_name'],
                        'pago_id'   => $pago->id,
                        'fecha'     => $viejo['autorizacion']['fecha'],
                    ]);
                }

                // Store proof files (one or many) uploaded for this payment
                $this->guardarComprobantesPago($pago, $request, $i);

                if ($entidad) {
                    if ($entidad->tangible) {
                        // Cash payment requires an open cash register
                        // Regla de negocio: una sola caja por sucursal y por día.
                        $cajaAbierta = Caja::abiertaDelDia($sucursalCaja);

                        if (!$cajaAbierta) {
                            throw new \Exception("No hay caja abierta para esta sucursal. No se puede registrar el pago en efectivo.");
                        }

                        // Cash payment: impacts physical cash register
                        MovimientoCaja::create([
                            'caja_id'        => $cajaAbierta->id,
                            'concepto_id'    => $conceptoVenta->id,
                            'entidad_id'     => $entidad->id,
                            'venta_pieza_id' => $venta->id,
                            'tipo'           => 'Ingreso',
                            'monto'          => $pago->monto,
                            'acreditado'     => 1,
                            'fecha'          => now(),
                            'user_id'        => $request->user_id,
                            'referencia'     => $pago->detalle,
                        ]);
                    }
                    if ($entidad->cuenta) {
                        // Non-tangible payment: impacts entity account only
                        \App\Models\MovimientoCuenta::create([
                            'entidad_id'     => $entidad->id,
                            'tipo'           => 'Ingreso',
                            'monto'          => $pago->monto,
                            'fecha'          => $pago->fecha,
                            'concepto'       => $conceptoVenta->nombre,
                            'venta_pieza_id' => $venta->id,
                            'pago_id'        => $pago->id,
                            'user_id'        => $request->user_id,
                        ]);
                    }
                }
            }
        }

        return $venta;
    }

    public function store(Request $request)
    {
        $rules = [
            'user_id'      => 'required',
            'fecha'        => 'required|date',
            'pieza_id'     => 'required|array|min:1',
            'pieza_id.*'   => 'required|distinct',
            'comprobante.*' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ];

        $messages = [
            'fecha.required'        => 'La fecha es obligatoria.',
            'pieza_id.required'     => 'Debe agregar al menos una pieza.',
            'pieza_id.min'          => 'Debe agregar al menos una pieza.',
            'pieza_id.*.required'   => 'Debe seleccionar una pieza.',
            'pieza_id.*.distinct'   => 'No puede repetir piezas.',
            'comprobante.*.mimes'   => 'El comprobante debe ser JPG, PNG o PDF.',
            'comprobante.*.max'     => 'El comprobante no puede superar los 5MB.',
        ];

        switch ($request->input('destino')) {
            case 'Salón':
                $rules['cliente_id'] = 'required';
                $messages['cliente_id.required'] = 'El campo Cliente es obligatorio.';
                break;
            case 'Sucursal':
                $rules['sucursal_id'] = 'required';
                $messages['sucursal_id.required'] = 'Debe seleccionar una sucursal.';
                break;
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $this->guardarVenta($request);
            DB::commit();
            return redirect()->route('ventaPiezas.index')->with('success', 'Registro creado satisfactoriamente');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => $ex->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'user_id'      => 'required',
            'fecha'        => 'required|date',
            'pieza_id'     => 'required|array|min:1',
            'pieza_id.*'   => 'required|distinct',
            'comprobante.*' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ];

        $messages = [
            'fecha.required'        => 'La fecha es obligatoria.',
            'pieza_id.required'     => 'Debe agregar al menos una pieza.',
            'pieza_id.min'          => 'Debe agregar al menos una pieza.',
            'pieza_id.*.required'   => 'Debe seleccionar una pieza.',
            'pieza_id.*.distinct'   => 'No puede repetir piezas.',
            'comprobante.*.mimes'   => 'El comprobante debe ser JPG, PNG o PDF.',
            'comprobante.*.max'     => 'El comprobante no puede superar los 5MB.',
        ];

        switch ($request->input('destino')) {
            case 'Salón':
                $rules['cliente_id'] = 'required';
                $messages['cliente_id.required'] = 'El campo Cliente es obligatorio.';
                break;
            case 'Sucursal':
                $rules['sucursal_id'] = 'required';
                $messages['sucursal_id.required'] = 'Debe seleccionar una sucursal.';
                break;
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Nunca se altera una caja ya cerrada: si el cobro impactó en una, la
        // edición se rechaza y el ajuste se hace a mano desde caja.
        if ($mensajeCaja = $this->movimientosBloqueadosPorCajaCerrada('venta_pieza_id', $id)) {
            return redirect()->back()->withErrors($mensajeCaja)->withInput();
        }

        DB::beginTransaction();
        try {
            // Se actualiza en el lugar: la venta conserva su id para no dejar
            // huérfanos los movimientos de caja y de cuenta que la referencian.
            $venta = VentaPieza::with([
                'piezas.pieza',
                'piezas.sucursal',
                'pagos.comprobantes',
                'pagos.autorizacion',
            ])->findOrFail($id);

            // Lo que el vendedor no puede tocar, indexado por posición del pago
            $preservado = $venta->pagos->values()->map(function ($pago) {
                $autorizacion = $pago->autorizacion;

                return [
                    'pagado'       => $pago->pagado,
                    'contadora'    => $pago->contadora,
                    'comprobantes' => $pago->comprobantes->pluck('path')->all(),
                    'autorizacion' => $autorizacion ? [
                        'user_id'   => $autorizacion->user_id,
                        'user_name' => $autorizacion->user_name,
                        'fecha'     => $autorizacion->fecha,
                    ] : null,
                ];
            })->all();

            foreach ($venta->piezas as $pvp) {
                if ($pvp->cantidad > 0) {
                    $stock = StockPieza::where('pieza_id', $pvp->pieza_id)
                        ->where('sucursal_id', $pvp->sucursal_id)
                        ->first();

                    if ($stock) {
                        $stock->cantidad += $pvp->cantidad;
                        $stock->save();
                    } else {
                        StockPieza::create([
                            'pieza_id'       => $pvp->pieza_id,
                            'sucursal_id'    => $pvp->sucursal_id,
                            'cantidad'       => $pvp->cantidad,
                            'remito'         => 'venta anulada',
                            'ingreso'        => Carbon::now()->toDateString(),
                            'costo'          => $pvp->pieza->costo ?? 0,
                            'precio_minimo'  => $pvp->pieza->precio_minimo ?? 0,
                            'proveedor'      => null,
                        ]);
                    }
                }
            }

            PiezaVentaPieza::where('venta_pieza_id', $venta->id)->delete();

            // Las autorizaciones se borran antes que los pagos para no quedar
            // huérfanas; guardarVenta() las vuelve a crear sobre los pagos nuevos.
            \App\Models\Autorizacion::whereIn('pago_id', $venta->pagos->pluck('id'))->delete();
            Pago::where('venta_pieza_id', $venta->id)->delete();

            // Baja de los movimientos viejos: si no, al recrearlos la plata queda
            // duplicada en la caja y en la cuenta de la entidad.
            $this->bajaMovimientosOperacion('venta_pieza_id', $venta->id);

            $this->guardarVenta($request, $venta, $preservado);

            DB::commit();
            return redirect()->route('ventaPiezas.index')->with('success', 'Registro actualizado correctamente');

        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al actualizar: ' . $ex->getMessage());
        }
    }


    public function edit($id)
    {
        $ventaPieza = VentaPieza::with(['piezas', 'piezas.pieza', 'piezas.sucursal', 'pagos', 'cliente'])->findOrFail($id);

        $user = auth()->user();
        $esAdministrador = $user->hasRole('Administrador');

        $stockPiezasQuery = StockPieza::with(['pieza', 'sucursal']);

        // Non-admin users see only stock from their own branch
        if (!$esAdministrador) {
            $stockPiezasQuery->where('sucursal_id', $user->sucursal_id);
        }
        $stockPiezas = $stockPiezasQuery
            ->get()
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

        $stockPiezasJson = $stockPiezas->groupBy('id');

        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $entidads = \App\Models\Entidad::orderBy('nombre')->where('activa', 1)->get(['id', 'nombre', 'forma', 'autorizacion']);
        return view('ventaPiezas.edit', compact('ventaPieza', 'users', 'stockPiezasJson', 'sucursals', 'provincias', 'entidads'));
    }


    public function show($id)
    {
        $ventaPieza = VentaPieza::with(['piezas', 'piezas.pieza', 'piezas.sucursal', 'pagos', 'pagos.entidad', 'pagos.comprobantes'])->findOrFail($id);

        // Cliente name via the relationship (the model also has a legacy free-text "cliente" column
        // that shadows the relation, so resolve it explicitly by cliente_id).
        $clienteNombre = optional($ventaPieza->cliente()->first())->nombre;

        $user = auth()->user();
        $esAdministrador = $user->hasRole('Administrador');

        $stockPiezasQuery = StockPieza::with(['pieza', 'sucursal']);

        // Non-admin users see only stock from their own branch
        if (!$esAdministrador) {
            $stockPiezasQuery->where('sucursal_id', $user->sucursal_id);
        }
        $stockPiezas = $stockPiezasQuery
            ->get()
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

        $stockPiezasJson = $stockPiezas->groupBy('id');

        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('ventaPiezas.show', compact('ventaPieza', 'users', 'stockPiezasJson', 'sucursals', 'clienteNombre'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        DB::transaction(function () use ($id) {
            $venta = VentaPieza::with('piezas.pieza', 'piezas.sucursal')->findOrFail($id);

            foreach ($venta->piezas as $pvp) {
                if ($pvp->cantidad > 0) {
                    // Sumar stock existente o crear uno nuevo
                    $stock = StockPieza::where('pieza_id', $pvp->pieza_id)
                        ->where('sucursal_id', $pvp->sucursal_id)
                        ->first();

                    if ($stock) {
                        $stock->cantidad += $pvp->cantidad;
                        $stock->save();
                    } else {
                        StockPieza::create([
                            'pieza_id' => $pvp->pieza_id,
                            'sucursal_id' => $pvp->sucursal_id,
                            'cantidad' => $pvp->cantidad,
                            'remito' => 'venta anulada',
                            'ingreso' => Carbon::now()->toDateString(),
                            'costo' => $pvp->pieza->costo ?? 0,
                            'precio_minimo' => $pvp->pieza->precio_minimo ?? 0,
                            'proveedor' => null,
                        ]);
                    }
                }
            }

            // Eliminar relaciones
            PiezaVentaPieza::where('venta_pieza_id', $venta->id)->delete();
            \App\Models\Pago::where('venta_pieza_id', $venta->id)->delete();
            // Eliminar la venta
            $venta->delete();
        });

        return redirect()->route('ventaPiezas.index')
            ->with('success','Venta pieza anulada con éxito');
    }

    public function generatePDF(Request $request,$attach = false)
    {
        $ventaPiezaId = $request->query('venta_pieza_id');
        $ventaPieza = VentaPieza::find($ventaPiezaId);



        $template = 'ventaPiezas.pdf';
        /*$unidadMovimientos = $ventaPieza->unidadMovimientos()->get();*/
        $destino='';
        $descripcion='';
        switch ($ventaPieza->destino) {
            case 'Salón':
                $destino ='Apellido y Nombre: '.$ventaPieza->cliente.'<br>Moto: '.$ventaPieza->moto.'<br>Documento: '.$ventaPieza->documento.'<br>Tel: '.$ventaPieza->telefono;
                $descripcion='Descripción:<br>'.$ventaPieza->descripcion;
                break;

            case 'Sucursal':
                $destino ='Sucursal: '.$ventaPieza->sucursal->nombre;
                $descripcion='Nro. de Reparación: '.$ventaPieza->pedido;
                break;

            case 'Taller':
                $destino ='Destino: Taller';
                $descripcion='Nro. de Reparación: '.$ventaPieza->pedido;
                break;
        }


        $data = [
            'remito' => str_pad($ventaPieza->id,8,'0',STR_PAD_LEFT),
            'fecha' => $ventaPieza->fecha,
            'destino' => $destino,
            'vendedor' => (isset($ventaPieza->user))?$ventaPieza->user->name:$ventaPieza->user_name,
            'piezaVentapiezas' => $ventaPieza->piezas,
            'descripcion' => $descripcion,
        ];
        //dd($data);




        $pdf = PDF::loadView($template, $data);

        $pdfPath = 'Venta_Pieza_' . $ventaPiezaId . '.pdf';

        if ($attach) {
            $fullPath = public_path('/temp/' . $pdfPath);
            $pdf->save($fullPath);
            return $fullPath; // Devuelve la ruta del archivo para su uso posterior
        } else {

            return $pdf->download($pdfPath);
        }

        // Renderiza la vista de previsualización para HTML
        //return view('integrantes.alta', $data);
    }

    public function exportarXLS(Request $request)
    {
        $columnas = [  'venta_piezas.fecha',DB::raw("IFNULL(clientes.nombre, IFNULL(clientes_serv.nombre, venta_piezas.cliente))"),DB::raw("CASE WHEN venta_piezas.servicio_id IS NOT NULL THEN NULLIF(TRIM(CONCAT_WS(' ', (SELECT m.nombre FROM marcas m WHERE m.id = servicios.marca_id), IFNULL((SELECT mo.nombre FROM modelos mo WHERE mo.id = servicios.modelo_id), servicios.modelo), NULLIF(servicios.motor, ''))), '') ELSE NULL END"),'venta_piezas.destino',DB::raw("(
        SELECT SUM(pvp.precio * pvp.cantidad)
        FROM pieza_venta_piezas pvp
        WHERE pvp.venta_pieza_id = venta_piezas.id
    ) as precio_total"),DB::raw("IFNULL((SELECT GROUP_CONCAT(DISTINCT s.nombre SEPARATOR ', ') FROM pieza_venta_piezas pvp INNER JOIN sucursals s ON s.id = pvp.sucursal_id WHERE pvp.venta_pieza_id = venta_piezas.id), IFNULL(suc_serv.nombre, sucursals.nombre))"), DB::raw("IFNULL(users.name, venta_piezas.user_name)"),
            DB::raw("(
    SELECT GROUP_CONCAT(p.codigo SEPARATOR ', ')
    FROM pieza_venta_piezas pvp
    INNER JOIN piezas p ON p.id = pvp.pieza_id
    WHERE pvp.venta_pieza_id = venta_piezas.id
) as piezas_codigos"),
            DB::raw($this->autorizacionCase())

        ]; // Define las columnas disponibles

        $busqueda = $request->search;
        $user_id = $request->user_id;
        $fechaDesde = $request->desde;
        $fechaHasta = $request->hasta;


        // ------------------------------
        // OBTENER NOMBRES DE LOS FILTROS
        // ------------------------------
        $userNombre = ($user_id && $user_id != -1)
            ? (User::find($user_id)->nombre ?? '—')
            : 'Todos';



        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = VentaPieza::select('venta_piezas.id as id', 'venta_piezas.fecha',DB::raw("IFNULL(clientes.nombre, IFNULL(clientes_serv.nombre, venta_piezas.cliente)) as cliente"),DB::raw("CASE WHEN venta_piezas.servicio_id IS NOT NULL THEN NULLIF(TRIM(CONCAT_WS(' ', (SELECT m.nombre FROM marcas m WHERE m.id = servicios.marca_id), IFNULL((SELECT mo.nombre FROM modelos mo WHERE mo.id = servicios.modelo_id), servicios.modelo), NULLIF(servicios.motor, ''))), '') ELSE NULL END as orden_servicio"),'venta_piezas.destino',DB::raw("(
            SELECT SUM(pvp.precio * pvp.cantidad)
            FROM pieza_venta_piezas pvp
            WHERE pvp.venta_pieza_id = venta_piezas.id
        ) as precio_total"),DB::raw("IFNULL((SELECT GROUP_CONCAT(DISTINCT s.nombre SEPARATOR ', ') FROM pieza_venta_piezas pvp INNER JOIN sucursals s ON s.id = pvp.sucursal_id WHERE pvp.venta_pieza_id = venta_piezas.id), IFNULL(suc_serv.nombre, sucursals.nombre)) as sucursal_nombre"),DB::raw("IFNULL(users.name, venta_piezas.user_name) as usuario_nombre"),
            DB::raw("(
    SELECT GROUP_CONCAT(p.codigo SEPARATOR ', ')
    FROM pieza_venta_piezas pvp
    INNER JOIN piezas p ON p.id = pvp.pieza_id
    WHERE pvp.venta_pieza_id = venta_piezas.id
) as piezas_codigos"),
            DB::raw($this->autorizacionCase('autorizacion'))

        )
            ->leftJoin('sucursals', 'venta_piezas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('clientes', 'venta_piezas.cliente_id', '=', 'clientes.id')
            ->leftJoin('servicios', 'venta_piezas.servicio_id', '=', 'servicios.id')
            ->leftJoin('clientes as clientes_serv', 'servicios.cliente_id', '=', 'clientes_serv.id')
            ->leftJoin('sucursals as suc_serv', 'servicios.sucursal_id', '=', 'suc_serv.id')
            ->leftJoin('users', 'venta_piezas.user_id', '=', 'users.id')
        ;

        if (!empty($user_id) && $user_id != '-1') {
            $query->where('venta_piezas.user_id', $user_id);
        }


        if (!empty($fechaDesde)) {
            $query->whereDate('venta_piezas.fecha', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('venta_piezas.fecha', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $piezas = $query->get();

        // ===============================
        //     📄 CREAR ARCHIVO XLSX
        // ===============================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Venta Piezas");

        // ------------------------------
        // FILTROS
        // ------------------------------
        $sheet->setCellValue('A1', 'Vendedor:');
        $sheet->setCellValue('B1', $userNombre);

        $sheet->setCellValue('A2', 'Desde:');
        $sheet->setCellValue('B2', $fechaDesde
            ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y')
            : '—');

        $sheet->setCellValue('A3', 'Hasta:');
        $sheet->setCellValue('B3', $fechaHasta
            ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y')
            : '—');


        $sheet->setCellValue('A4', 'Búsqueda:');
        $sheet->setCellValue('B4', $busqueda ?: '—');

        // Espacio antes de la tabla
        $startRow = 5;

        // ------------------------------
        // ENCABEZADOS DE LA TABLA
        // ------------------------------
        $headers = [
            "Fecha", "Cliente", "Orden de Servicio", "Destino",
            "Monto", "Sucursal", "Vendedor", "Piezas","Estado"
        ];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $startRow, $header);
            $sheet->getStyleByColumnAndRow($col, $startRow)->getFont()->setBold(true);
            $col++;
        }

        // ------------------------------
        // DATOS
        // ------------------------------
        $row = $startRow + 1;

        foreach ($piezas as $p) {
            // 🟢 Formato de fecha dd/mm/YYYY
            $sheet->setCellValue("A{$row}",
                $p->fecha
                    ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y')
                    : '—'
            );

            $sheet->setCellValue("B{$row}", $p->cliente);
            $sheet->setCellValue("C{$row}", $p->orden_servicio);
            $sheet->setCellValue("D{$row}", $p->destino);
            $sheet->setCellValue("E{$row}", $p->precio_total);
            $sheet->setCellValue("F{$row}", $p->sucursal_nombre);
            $sheet->setCellValue("G{$row}", $p->usuario_nombre);
            $sheet->setCellValue("H{$row}", $p->piezas_codigos);
            $sheet->setCellValue("I{$row}", $p->autorizacion);

            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "venta_piezas.xlsx";
        $filePath = tempnam(sys_get_temp_dir(), $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }






    public function exportarPDF(Request $request)
    {
        ini_set('memory_limit', '-1'); // ilimitado
        ini_set('max_execution_time', 0);

        $columnas = [  'venta_piezas.fecha',DB::raw("IFNULL(clientes.nombre, IFNULL(clientes_serv.nombre, venta_piezas.cliente))"),DB::raw("CASE WHEN venta_piezas.servicio_id IS NOT NULL THEN NULLIF(TRIM(CONCAT_WS(' ', (SELECT m.nombre FROM marcas m WHERE m.id = servicios.marca_id), IFNULL((SELECT mo.nombre FROM modelos mo WHERE mo.id = servicios.modelo_id), servicios.modelo), NULLIF(servicios.motor, ''))), '') ELSE NULL END"),'venta_piezas.destino',DB::raw("(
        SELECT SUM(pvp.precio * pvp.cantidad)
        FROM pieza_venta_piezas pvp
        WHERE pvp.venta_pieza_id = venta_piezas.id
    ) as precio_total"),DB::raw("IFNULL((SELECT GROUP_CONCAT(DISTINCT s.nombre SEPARATOR ', ') FROM pieza_venta_piezas pvp INNER JOIN sucursals s ON s.id = pvp.sucursal_id WHERE pvp.venta_pieza_id = venta_piezas.id), IFNULL(suc_serv.nombre, sucursals.nombre))"), DB::raw("IFNULL(users.name, venta_piezas.user_name)"),
            DB::raw($this->autorizacionCase()),
            DB::raw("(
    SELECT GROUP_CONCAT(p.codigo SEPARATOR ', ')
    FROM pieza_venta_piezas pvp
    INNER JOIN piezas p ON p.id = pvp.pieza_id
    WHERE pvp.venta_pieza_id = venta_piezas.id
) as piezas_codigos")


        ]; // Define las columnas disponibles

        $busqueda = $request->search;
        $user_id = $request->user_id;
        $fechaDesde = $request->desde;
        $fechaHasta = $request->hasta;


        // ------------------------------
        // OBTENER NOMBRES DE LOS FILTROS
        // ------------------------------
        $userNombre = ($user_id && $user_id != -1)
            ? (User::find($user_id)->nombre ?? '—')
            : 'Todos';



        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = VentaPieza::select('venta_piezas.id as id', 'venta_piezas.fecha',DB::raw("IFNULL(clientes.nombre, IFNULL(clientes_serv.nombre, venta_piezas.cliente)) as cliente"),DB::raw("CASE WHEN venta_piezas.servicio_id IS NOT NULL THEN NULLIF(TRIM(CONCAT_WS(' ', (SELECT m.nombre FROM marcas m WHERE m.id = servicios.marca_id), IFNULL((SELECT mo.nombre FROM modelos mo WHERE mo.id = servicios.modelo_id), servicios.modelo), NULLIF(servicios.motor, ''))), '') ELSE NULL END as orden_servicio"),'venta_piezas.destino',DB::raw("(
            SELECT SUM(pvp.precio * pvp.cantidad)
            FROM pieza_venta_piezas pvp
            WHERE pvp.venta_pieza_id = venta_piezas.id
        ) as precio_total"),DB::raw("IFNULL((SELECT GROUP_CONCAT(DISTINCT s.nombre SEPARATOR ', ') FROM pieza_venta_piezas pvp INNER JOIN sucursals s ON s.id = pvp.sucursal_id WHERE pvp.venta_pieza_id = venta_piezas.id), IFNULL(suc_serv.nombre, sucursals.nombre)) as sucursal_nombre"),DB::raw("IFNULL(users.name, venta_piezas.user_name) as usuario_nombre"),
            DB::raw("(
    SELECT GROUP_CONCAT(p.codigo SEPARATOR ', ')
    FROM pieza_venta_piezas pvp
    INNER JOIN piezas p ON p.id = pvp.pieza_id
    WHERE pvp.venta_pieza_id = venta_piezas.id
) as piezas_codigos"),
            DB::raw($this->autorizacionCase('autorizacion'))

        )
            ->leftJoin('sucursals', 'venta_piezas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('clientes', 'venta_piezas.cliente_id', '=', 'clientes.id')
            ->leftJoin('servicios', 'venta_piezas.servicio_id', '=', 'servicios.id')
            ->leftJoin('clientes as clientes_serv', 'servicios.cliente_id', '=', 'clientes_serv.id')
            ->leftJoin('sucursals as suc_serv', 'servicios.sucursal_id', '=', 'suc_serv.id')
            ->leftJoin('users', 'venta_piezas.user_id', '=', 'users.id')
        ;

        if (!empty($user_id) && $user_id != '-1') {
            $query->where('venta_piezas.user_id', $user_id);
        }


        if (!empty($fechaDesde)) {
            $query->whereDate('venta_piezas.fecha', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('venta_piezas.fecha', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $piezas = $query->get();

        // Pasamos datos a la vista PDF
        $data = [
            'piezas' => $piezas,
            'busqueda' => $busqueda,
            'userNombre' => $userNombre,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ];

        $pdf = PDF::loadView('ventaPiezas.exportpdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('ventaPiezas.exportpdf');
    }
}
