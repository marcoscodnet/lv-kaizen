<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Autorizacion;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Parametro;
use App\Models\Provincia;
use App\Models\Sucursal;
use App\Models\Unidad;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaPieza;
use App\Models\PiezaVentaPieza;
use App\Models\Entidad;
use App\Models\Documento;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Concepto;
use App\Traits\CatalogoArticulos;
use App\Traits\RehaceMovimientos;
use App\Traits\StockArticulos;
use App\Traits\SanitizesInput;
use App\Traits\ValidaCajaAbierta;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use setasign\Fpdi\Fpdi;

class VentaController extends Controller
{
    use SanitizesInput, RehaceMovimientos, ValidaCajaAbierta, CatalogoArticulos, StockArticulos;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:venta-listar|venta-crear|venta-editar|venta-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:venta-crear', ['only' => ['create','store']]);
        $this->middleware('permission:venta-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:venta-eliminar', ['only' => ['destroy']]);
    }

    // SQL CASE that returns 'Autorizada' when the sale has payments and all of them are authorized
    private function autorizacionCase(string $alias = ''): string
    {
        $as = $alias ? " as $alias" : '';
        return "CASE
            WHEN EXISTS (SELECT 1 FROM pagos WHERE pagos.venta_id = ventas.id)
             AND NOT EXISTS (
                 SELECT 1 FROM pagos p2
                 JOIN entidads e2 ON e2.id = p2.entidad_id
                 LEFT JOIN autorizacions a2 ON a2.pago_id = p2.id
                 WHERE p2.venta_id = ventas.id
                   AND e2.autorizacion = 1
                   AND a2.id IS NULL
             )
            THEN 'Autorizada' ELSE 'No autorizada' END{$as}";
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
     * Guarda los conceptos que se cargaron junto con la moto —patentamiento,
     * seguro, casco— como una venta de artículos colgada de esta venta.
     *
     * Esa venta de artículos NO lleva pagos propios: la plata vive toda en la
     * venta de la moto. Por eso hay un solo importe a cobrar y una sola
     * autorización, y no aparece suelta en el listado de artículos.
     *
     * Al editar se repone el stock de lo anterior y se descuenta lo nuevo, así
     * que sirve igual para alta y para modificación.
     *
     * @throws \Exception si falta stock de algún artículo que lleva existencias
     */
    private function sincronizarArticulos(Request $request, Venta $venta): void
    {
        $piezaIds   = (array) $request->input('pieza_id', []);
        $sucursales = (array) $request->input('sucursal_id_item', []);
        $cantidades = (array) $request->input('cantidad', []);
        $precios    = (array) $request->input('precio_articulo', []);

        // Las filas sin artículo elegido se descartan
        $filas = [];
        foreach ($piezaIds as $i => $piezaId) {
            if (empty($piezaId)) {
                continue;
            }

            $cantidad = (float) ($cantidades[$i] ?? 1);

            $filas[] = [
                'pieza_id'    => $piezaId,
                'sucursal_id' => $sucursales[$i] ?? $venta->sucursal_id,
                'cantidad'    => $cantidad > 0 ? $cantidad : 1,
                'precio'      => (float) $this->sanitizeInput($precios[$i] ?? 0),
            ];
        }

        $ventaArticulos = VentaPieza::with('piezas.pieza.tipoPieza')
            ->where('venta_id', $venta->id)
            ->first();

        // Lo que había vuelve al stock antes de rehacer el detalle
        if ($ventaArticulos) {
            $this->reponerStockArticulos($ventaArticulos->piezas);
            PiezaVentaPieza::where('venta_pieza_id', $ventaArticulos->id)->delete();
        }

        // Sin conceptos no hay nada que colgar de la venta
        if (empty($filas)) {
            if ($ventaArticulos) {
                $ventaArticulos->delete();
            }
            return;
        }

        $this->validarStockArticulos($filas);

        if (!$ventaArticulos) {
            $ventaArticulos = new VentaPieza();
            $ventaArticulos->venta_id = $venta->id;
        }

        $ventaArticulos->user_id     = $venta->user_id;
        $ventaArticulos->fecha       = $venta->fecha;
        $ventaArticulos->destino     = 'Salón';
        $ventaArticulos->cliente_id  = $venta->cliente_id;
        $ventaArticulos->sucursal_id = $venta->sucursal_id;
        $ventaArticulos->forma       = $venta->forma;
        $ventaArticulos->descripcion = 'Conceptos de la venta #' . $venta->id;
        $ventaArticulos->save();

        foreach ($filas as $fila) {
            $detalle = new PiezaVentaPieza();
            $detalle->venta_pieza_id = $ventaArticulos->id;
            $detalle->pieza_id       = $fila['pieza_id'];
            $detalle->sucursal_id    = $fila['sucursal_id'];
            $detalle->cantidad       = $fila['cantidad'];
            $detalle->precio         = $fila['precio'];
            $detalle->save();

            $this->descontarStockArticulo($fila);
        }
    }

    /**
     * Importe a cobrar de la operación según lo que viene en el formulario:
     * la moto más los conceptos.
     */
    private function importeACobrarDelRequest(Request $request): float
    {
        $moto = (float) $this->sanitizeInput(
            $request->filled('monto_unidad') ? $request->monto_unidad : $request->input('precio', 0)
        );

        $cantidades = (array) $request->input('cantidad', []);
        $precios    = (array) $request->input('precio_articulo', []);
        $conceptos  = 0.0;

        foreach ((array) $request->input('pieza_id', []) as $i => $piezaId) {
            if (empty($piezaId)) {
                continue;
            }

            $cantidad = (float) ($cantidades[$i] ?? 1);
            $conceptos += (float) $this->sanitizeInput($precios[$i] ?? 0) * ($cantidad > 0 ? $cantidad : 1);
        }

        return $moto + $conceptos;
    }

    /**
     * Neteo de la operación: los pagos cargados tienen que cubrir el importe a
     * cobrar. Si falta, no se confirma.
     *
     * Ojo, esto NO es lo mismo que el control de acreditación: acreditar es una
     * cuestión de tiempo (un cheque a dos días, un crédito a veinte) y ese sí es
     * informativo. Acá se controla que el vendedor haya cargado todo lo que
     * cobra, y eso no puede quedar corto.
     *
     * Cobrar de más no traba: se avisa en pantalla y se deja seguir.
     */
    private function validarCobroCompleto($validator, Request $request): void
    {
        $aCobrar = $this->importeACobrarDelRequest($request);

        if ($aCobrar <= 0) {
            return;
        }

        $cobrado = 0.0;
        foreach ((array) $request->input('monto', []) as $monto) {
            $cobrado += (float) $this->sanitizeInput($monto);
        }

        if ($cobrado >= $aCobrar - 0.01) {
            return;
        }

        $formato = function ($n) {
            return number_format((float) $n, 2, ',', '.');
        };

        $validator->errors()->add('cobro', sprintf(
            'Faltan $%s: el importe a cobrar es $%s y los pagos cargados suman $%s.',
            $formato($aCobrar - $cobrado),
            $formato($aCobrar),
            $formato($cobrado)
        ));
    }

    /**
     * Controla el stock de los conceptos ANTES de tocar la base, como un error
     * de validación más. Así el aviso sale junto al resto de los mensajes y no
     * se pierde lo que el vendedor cargó.
     */
    private function validarStockDeConceptos($validator, Request $request): void
    {
        $piezaIds   = (array) $request->input('pieza_id', []);
        $sucursales = (array) $request->input('sucursal_id_item', []);
        $cantidades = (array) $request->input('cantidad', []);

        $filas = [];
        foreach ($piezaIds as $i => $piezaId) {
            if (empty($piezaId)) {
                continue;
            }

            $cantidad = (float) ($cantidades[$i] ?? 1);

            $filas[] = [
                'pieza_id'    => $piezaId,
                'sucursal_id' => $sucursales[$i] ?? $request->sucursal_id,
                'cantidad'    => $cantidad > 0 ? $cantidad : 1,
            ];
        }

        if (empty($filas)) {
            return;
        }

        try {
            $this->validarStockArticulos($filas);
        } catch (\Exception $ex) {
            $validator->errors()->add('articulos', $ex->getMessage());
        }
    }

    /**
     * Deja en `ventas.total` el importe a cobrar de toda la operación: la moto
     * más los conceptos. Es el número contra el que se compara lo acreditado.
     */
    private function actualizarTotalVenta(Venta $venta): void
    {
        $venta->load('ventaArticulos.piezas');
        $venta->total = (float) $venta->monto + $venta->total_articulos;
        $venta->save();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = \App\Models\User::orderBy('name')
            ->pluck('name', 'id')
            ->prepend('Todos', '-1');
        //$ventas = Venta::all();
        $documentos = Documento::where('habilitado', 1)
            ->orderBy('orden')
            ->get();
        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1');
        return view ('ventas.index',compact('documentos','users','sucursals'));
    }


    public function dataTable(Request $request)
    {
        $columnas = [
            'ventas.fecha',
            'clientes.nombre',
            'unidads.motor',
            'modelos.nombre',
            DB::raw("IFNULL(users.name, ventas.user_name)"),
            'sucursals.nombre',
            DB::raw($this->autorizacionCase()),
        ];

        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');
        $user_id = $request->input('user_id');
        $sucursal_id = $request->input('sucursal_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        // Query base
        $query = Venta::select(
            'ventas.id as id',
            'ventas.fecha',
            'clientes.nombre as cliente',
            'unidads.motor',
            'modelos.nombre as modelo',
            DB::raw("IFNULL(users.name, ventas.user_name) as usuario_nombre"),
            'sucursals.nombre as sucursal_nombre',
            DB::raw($this->autorizacionCase('autorizacion'))
        )
            ->leftJoin('sucursals', 'ventas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->leftJoin('unidads', 'ventas.unidad_id', '=', 'unidads.id')
            ->leftJoin('productos', 'unidads.producto_id', '=', 'productos.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('users', 'ventas.user_id', '=', 'users.id');

        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('ventas.sucursal_id', $sucursal_id);
        }


        if (!empty($user_id) && $user_id != '-1') {
            $query->where('ventas.user_id', $user_id);
        }


        if (!empty($fechaDesde)) {
            $query->whereDate('ventas.fecha', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('ventas.fecha', '<=', $fechaHasta);
        }

        // Aplicar búsqueda
        if (!empty($busqueda)) {
            $query->where(function ($query) use ($columnas, $busqueda) {
                foreach ($columnas as $columna) {
                    if ($columna) {
                        $query->orWhere($columna, 'like', "%$busqueda%");
                    }
                }
            });
        }

        // Clonar para evitar pisar el query
        $baseQuery = clone $query;

        // Totales
        $totalVentas = (clone $baseQuery)->count();

        // A sale is authorized when it has payments and none of them are missing an authorization
        $ventasAutorizadas = (clone $baseQuery)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pagos')
                    ->whereColumn('pagos.venta_id', 'ventas.id');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pagos as p2')
                    ->leftJoin('autorizacions as a2', 'a2.pago_id', '=', 'p2.id')
                    ->whereColumn('p2.venta_id', 'ventas.id')
                    ->whereNull('a2.id');
            })
            ->count();

        $ventasNoAutorizadas = $totalVentas - $ventasAutorizadas;

        // IDs de ventas para pagos
        $ventaIds = (clone $baseQuery)->pluck('ventas.id');
        $totalAcreditado = Pago::whereIn('venta_id', $ventaIds)->sum('pagado');
        $totalVentasImporte = Pago::whereIn('venta_id', $ventaIds)->sum('monto');

        // Cantidad filtrada
        $recordsFiltered = (clone $baseQuery)->count();

        // Datos paginados
        $datos = (clone $baseQuery)
            ->orderBy($columnaOrden, $orden)
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Total sin filtros
        $recordsTotal = Venta::count();

        return response()->json([
            'data' => $datos,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
            'totales' => [
                'totalVentas' => $totalVentas,
                'ventasAutorizadas' => $ventasAutorizadas,
                'ventasNoAutorizadas' => $ventasNoAutorizadas,
                'totalAcreditado' => $totalAcreditado,
                'totalVentasImporte' => $totalVentasImporte
            ]
        ]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function unidads(Request $request)
    {

        $unidads = Unidad::all();
        return view ('ventas.unidads',compact('unidads'));
    }


    public function unidadDataTable(Request $request)
    {
        $columnas = ['tipo_unidads.nombre','marcas.nombre','modelos.nombre','colors.nombre','sucursals.nombre','unidads.ingreso','unidads.year','unidads.envio','unidads.motor','unidads.cuadro']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Unidad::select('unidads.id as id', 'tipo_unidads.nombre as tipo_unidad_nombre', 'marcas.nombre as marca_nombre', 'modelos.nombre as modelo_nombre', 'colors.nombre as color_nombre','sucursals.nombre as sucursal_nombre','unidads.ingreso','unidads.year','unidads.envio','unidads.motor','unidads.cuadro')
            ->leftJoin('productos', 'unidads.producto_id', '=', 'productos.id')
            ->leftJoin('sucursals', 'unidads.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('tipo_unidads', 'productos.tipo_unidad_id', '=', 'tipo_unidads.id')
            ->leftJoin('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('colors', 'productos.color_id', '=', 'colors.id')
            ->where('productos.discontinuo',0)
            ->whereNotIn('unidads.id', function ($q) {
                $q->select('unidad_id')->from('ventas');
            });


        // Aplicar la búsqueda
        if (!empty($busqueda)) {
            $query->where(function ($query) use ($columnas, $busqueda) {
                foreach ($columnas as $columna) {
                    if ($columna){
                        $query->orWhere($columna, 'like', "%$busqueda%");
                    }

                }
            });
        }




        // Obtener la cantidad total de registros después de aplicar el filtro de búsqueda
        $recordsFiltered = $query->count();


        $datos = $query->orderBy($columnaOrden, $orden)->skip($request->input('start'))->take($request->input('length'))->get();

        // Obtener la cantidad total de registros sin filtrar
        $recordsTotal = Unidad::count();



        return response()->json([
            'data' => $datos, // Obtener solo los elementos paginados
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
        ]);
    }

    public function vender($id)
    {
        $unidad = Unidad::find($id);
        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $entidads = \App\Models\Entidad::orderBy('nombre')->where('activa', 1)->get(['id', 'nombre', 'forma', 'autorizacion']);
        // Catálogo para la grilla de conceptos (patentamiento, seguro, casco)
        $articulosJson = $this->catalogoArticulos(true);

        return view('ventas.vender', compact('users','sucursals', 'unidad','provincias','entidads','articulosJson'));
    }

    public function store(Request $request)
    {
        $precioSugerido = $request->input('precio', 0);
        $totalMonto = $request->input('totalMonto', 0);

        $rules = [
            'unidad_id' => 'required',
            'user_id' => 'required',
            'cliente_id' => 'required',
            'sucursal_id' => 'required',
            'forma' => 'required',
            'monto_unidad' => 'required|numeric|min:0',
            'fecha' => 'required|date_format:d/m/Y H:i:s',
            'entidad_id' => 'required|array|min:1',
            'entidad_id.*' => 'required',
            'monto.*' => 'required|numeric|min:1',
            'fecha_pago.*' => 'required|date',
            'comprobante.*' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ];

        $messages = [
            'fecha.required' => 'La fecha es obligatoria.',
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'entidad_id.required' => 'Debe agregar al menos un pago.',
            'entidad_id.min' => 'Debe agregar al menos un pago.',
            'entidad_id.*.required' => 'Debe seleccionar una forma de pago.',
            'monto.*.required' => 'El importe es obligatorio.',
            'fecha_pago.*.required' => 'La fecha de pago es obligatoria.',
            'comprobante.*.mimes' => 'El comprobante debe ser JPG, PNG o PDF.',
            'comprobante.*.max' => 'El comprobante no puede superar los 5MB.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // La caja se controla acá y no en medio del guardado: así el aviso sale
        // junto al resto de los errores y no se pierde lo que cargó el usuario.
        $validator->after(function ($validator) use ($request) {
            if ($mensajeCaja = $this->faltaCajaAbierta($request->sucursal_id, (array) $request->input('entidad_id', []))) {
                $validator->errors()->add('caja', $mensajeCaja);
            }
        });

        // Los conceptos con existencias tampoco se controlan en medio del
        // guardado: si falta stock, sale como un mensaje más.
        $validator->after(function ($validator) use ($request) {
            $this->validarStockDeConceptos($validator, $request);
        });

        // El neteo de la operación: los pagos tienen que cubrir el importe a
        // cobrar. Cobrar de menos no se confirma; cobrar de más solo avisa.
        $validator->after(function ($validator) use ($request) {
            $this->validarCobroCompleto($validator, $request);
        });

        if ($validator->fails()) {
            $cliente = Cliente::find($request->input('cliente_id'));
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->all() + [
                        'cliente_nombre' => optional($cliente)->full_name_phone,
                    ]);
        }

        DB::beginTransaction();
        $ok = 1;
        try {
            $venta = new Venta();
            $venta->unidad_id = $this->sanitizeInput($request->unidad_id);
            $venta->user_id = $this->sanitizeInput($request->user_id);
            $venta->cliente_id = $this->sanitizeInput($request->cliente_id);
            $venta->sucursal_id = $this->sanitizeInput($request->sucursal_id);
            $venta->fecha = $request->filled('fecha')
                ? Carbon::createFromFormat('d/m/Y H:i:s', $request->fecha)->format('Y-m-d H:i:s')
                : null;
            // El vendedor tipea lo que cobra por la moto, independiente del
            // precio de lista. `total` se recalcula abajo con los conceptos.
            $venta->monto = $this->sanitizeInput($request->filled('monto_unidad')
                ? $request->monto_unidad
                : $request->precio);
            $venta->total = $venta->monto;
            $venta->forma = $this->sanitizeInput($request->forma);
            $venta->save();

            // Conceptos cargados junto con la moto (patentamiento, seguro, casco).
            // Van como venta de artículos colgada de esta venta, sin pagos propios.
            $this->sincronizarArticulos($request, $venta);
            $this->actualizarTotalVenta($venta);

            foreach ($request->entidad_id as $i => $entidadId) {
                $entidad = Entidad::find($entidadId);

                $detalle = new Pago();
                $detalle->venta_id = $venta->id;
                $detalle->entidad_id = $entidadId;
                $detalle->monto = $this->sanitizeInput($request->monto[$i]);
                $detalle->fecha = $this->sanitizeInput($request->fecha_pago[$i]);
                // Entidades sin autorización (efectivo y similares) no pasan por
                // auditoría: se acreditan solas por el importe cobrado y sin fecha
                // de contadora. El resto lo completa el auditor.
                if ($entidad && $entidad->acreditaAutomatico()) {
                    $detalle->pagado = $detalle->monto;
                    $detalle->contadora = null;
                }
                $detalle->detalle = $this->sanitizeInput($request->detalle[$i] ?? null);
                $detalle->observacion = $this->sanitizeInput($request->observaciones[$i] ?? null);

                $detalle->save();

                // Store proof files (one or many) uploaded for this payment
                $this->guardarComprobantesPago($detalle, $request, $i);

                $conceptoVenta = Concepto::firstOrCreate(['nombre' => 'Venta de unidad']);

                if ($entidad) {
                    if ($entidad->tangible) {
                        // Regla de negocio: una sola caja por sucursal y por día.
                        // El pago impacta la caja del día de la sucursal, sin importar qué usuario lo registre.
                        // Red de seguridad: el control de verdad es faltaCajaAbierta(), en la validación.
                        $cajaAbierta = Caja::abiertaDelDia($request->sucursal_id);

                        if (!$cajaAbierta) {
                            DB::rollBack();
                            return redirect()->back()
                                ->withErrors("No hay caja abierta para esta sucursal. No se puede registrar el pago en efectivo.")
                                ->withInput();
                        }

                        // Cash payment: impacts physical cash register
                        MovimientoCaja::create([
                            'caja_id'     => $cajaAbierta->id,
                            'concepto_id' => $conceptoVenta->id,
                            'entidad_id'  => $entidad->id,
                            'venta_id'    => $venta->id,
                            'tipo'        => 'Ingreso',
                            'monto'       => $detalle->monto,
                            'acreditado'  => 1,
                            'fecha'       => now(),
                            'user_id'     => $request->user_id,
                            'referencia'  => $detalle->detalle,
                        ]);
                    }
                    if ($entidad->cuenta) {
                        // Account payment: impacts entity account
                        \App\Models\MovimientoCuenta::create([
                            'entidad_id' => $entidad->id,
                            'tipo'       => 'Ingreso',
                            'monto'      => $detalle->monto,
                            'fecha'      => $detalle->fecha,
                            'concepto'   => $conceptoVenta->nombre,
                            'venta_id'   => $venta->id,
                            'pago_id'    => $detalle->id,
                            'user_id'    => $request->user_id,
                        ]);
                    }
                }
            }

        } catch (\Exception $ex) {
            // Incluye QueryException y el faltante de stock de los conceptos:
            // cualquiera de los dos tiene que hacer rollback, nunca un 500.
            $error = $ex->getMessage();
            $ok = 0;
        }

        if ($ok) {
            DB::commit();
            $respuestaID = 'success';
            $respuestaMSJ = 'Registro creado satisfactoriamente';
        } else {
            DB::rollback();
            $respuestaID = 'error';
            $respuestaMSJ = $error;
        }

        return redirect()->route('ventas.index')->with($respuestaID, $respuestaMSJ);
    }

    public function edit($id) {
        $venta = Venta::with('pagos', 'unidad', 'cliente', 'ventaArticulos.piezas.pieza')->findOrFail($id);
        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $entidads = \App\Models\Entidad::orderBy('nombre')->where('activa', 1)->get(['id', 'nombre', 'forma', 'autorizacion']);

        // Catálogo completo: al editar, un artículo agotado tiene que seguir apareciendo
        $articulosJson = $this->catalogoArticulos(false);

        return view('ventas.edit', compact('venta', 'users', 'sucursals', 'entidads','provincias','articulosJson'));
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::with('pagos.comprobantes', 'pagos.autorizacion')->findOrFail($id);

        $precioSugerido = $request->input('precio', 0);
        $totalMonto = $request->input('totalMonto', 0);

        $rules = [
            'unidad_id' => 'required',
            'user_id' => 'required',
            'cliente_id' => 'required',
            'sucursal_id' => 'required',
            'forma' => 'required',
            'monto_unidad' => 'required|numeric|min:0',
            'fecha' => 'required|date_format:d/m/Y H:i:s',
            'entidad_id' => 'required|array|min:1',
            'entidad_id.*' => 'required',
            'monto.*' => 'required|numeric|min:0',
            'fecha_pago.*' => 'required|date',
            'comprobante.*' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ];

        $messages = [
            'fecha.required' => 'La fecha es obligatoria.',
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'entidad_id.required' => 'Debe agregar al menos un pago.',
            'entidad_id.min' => 'Debe agregar al menos un pago.',
            'entidad_id.*.required' => 'Debe seleccionar una forma de pago.',
            'monto.*.required' => 'El importe es obligatorio.',
            'fecha_pago.*.required' => 'La fecha de pago es obligatoria.',
            'monto.*.min' => 'El importe es obligatorio.',
            'comprobante.*.mimes' => 'El comprobante debe ser JPG, PNG o PDF.',
            'comprobante.*.max' => 'El comprobante no puede superar los 5MB.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // La caja se controla acá y no en medio del guardado: así el aviso sale
        // junto al resto de los errores y no se pierde lo que cargó el usuario.
        $validator->after(function ($validator) use ($request) {
            if ($mensajeCaja = $this->faltaCajaAbierta($request->sucursal_id, (array) $request->input('entidad_id', []))) {
                $validator->errors()->add('caja', $mensajeCaja);
            }
        });

        // Los conceptos con existencias tampoco se controlan en medio del
        // guardado: si falta stock, sale como un mensaje más.
        $validator->after(function ($validator) use ($request) {
            $this->validarStockDeConceptos($validator, $request);
        });

        // El neteo de la operación: los pagos tienen que cubrir el importe a
        // cobrar. Cobrar de menos no se confirma; cobrar de más solo avisa.
        $validator->after(function ($validator) use ($request) {
            $this->validarCobroCompleto($validator, $request);
        });

        if ($validator->fails()) {
            $cliente = Cliente::find($request->input('cliente_id'));
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->all() + [
                        'cliente_nombre' => optional($cliente)->full_name_phone,
                    ]);
        }

        // Nunca se altera una caja ya cerrada: si el cobro impactó en una, la
        // edición se rechaza y el ajuste se hace a mano desde caja.
        if ($mensajeCaja = $this->movimientosBloqueadosPorCajaCerrada('venta_id', $venta->id)) {
            $cliente = Cliente::find($request->input('cliente_id'));
            return redirect()->back()
                ->withErrors($mensajeCaja)
                ->withInput($request->all() + [
                        'cliente_nombre' => optional($cliente)->full_name_phone,
                    ]);
        }

        DB::beginTransaction();
        $ok = 1;
        try {
            // Baja de los movimientos viejos: si no, al recrearlos la plata queda
            // duplicada en la caja y en la cuenta de la entidad.
            $this->bajaMovimientosOperacion('venta_id', $venta->id);

            $venta->unidad_id = $this->sanitizeInput($request->unidad_id);
            $venta->user_id = $this->sanitizeInput($request->user_id);
            $venta->cliente_id = $this->sanitizeInput($request->cliente_id);
            $venta->sucursal_id = $this->sanitizeInput($request->sucursal_id);
            $venta->fecha = $request->filled('fecha')
                ? Carbon::createFromFormat('d/m/Y H:i:s', $request->fecha)->format('Y-m-d H:i:s')
                : null;
            // El vendedor tipea lo que cobra por la moto, independiente del
            // precio de lista. `total` se recalcula abajo con los conceptos.
            $venta->monto = $this->sanitizeInput($request->filled('monto_unidad')
                ? $request->monto_unidad
                : $request->precio);
            $venta->total = $venta->monto;
            $venta->forma = $this->sanitizeInput($request->forma);
            $venta->save();

            // Conceptos cargados junto con la moto (patentamiento, seguro, casco).
            // Van como venta de artículos colgada de esta venta, sin pagos propios.
            $this->sincronizarArticulos($request, $venta);
            $this->actualizarTotalVenta($venta);

            // Preserve auditor data and existing proofs before deleting payments
            // Map old payments by their index order so we can re-link proofs/audit data
            $pagosViejos = $venta->pagos->values();

            // Capture existing proof paths (indexed like the payments) before deleting,
            // since comprobantes rows cascade-delete when their payments are removed.
            $comprobantesViejos = $pagosViejos->map(function ($p) {
                return $p->comprobantes->pluck('path')->all();
            })->all();

            // Igual con la autorización del auditor: se borra antes que los pagos
            // para no quedar huérfana, y se vuelve a crear sobre el pago nuevo.
            $autorizacionesViejas = $pagosViejos->map(function ($p) {
                $a = $p->autorizacion;
                return $a ? [
                    'user_id'   => $a->user_id,
                    'user_name' => $a->user_name,
                    'fecha'     => $a->fecha,
                ] : null;
            })->all();

            Autorizacion::whereIn('pago_id', $pagosViejos->pluck('id'))->delete();
            $venta->pagos()->delete();

            foreach ($request->entidad_id as $i => $entidadId) {
                $pagoViejo = $pagosViejos[$i] ?? null;
                $entidad = Entidad::find($entidadId);

                $detalle = new Pago();
                $detalle->venta_id = $venta->id;
                $detalle->entidad_id = $entidadId;
                $detalle->monto = $this->sanitizeInput($request->monto[$i]);
                $detalle->fecha = $this->sanitizeInput($request->fecha_pago[$i]);
                $detalle->detalle = $this->sanitizeInput($request->detalle[$i] ?? null);
                $detalle->observacion = $this->sanitizeInput($request->observaciones[$i] ?? null);

                if ($entidad && $entidad->acreditaAutomatico()) {
                    // Entidad sin autorización: se acredita sola por el importe
                    // cobrado, aunque el vendedor haya cambiado el monto.
                    $detalle->pagado = $detalle->monto;
                    $detalle->contadora = null;
                } elseif ($pagoViejo) {
                    // Keep auditor fields (pagado/contadora) from the old payment, seller does not touch them
                    $detalle->pagado = $pagoViejo->pagado;
                    $detalle->contadora = $pagoViejo->contadora;
                }

                $detalle->save();

                // Re-link the proofs that already existed for this payment row
                foreach (($comprobantesViejos[$i] ?? []) as $pathViejo) {
                    \App\Models\Comprobante::create([
                        'pago_id' => $detalle->id,
                        'path'    => $pathViejo,
                    ]);
                }

                // Re-link the auditor's authorization for this payment row
                if (!empty($autorizacionesViejas[$i])) {
                    Autorizacion::create([
                        'user_id'   => $autorizacionesViejas[$i]['user_id'],
                        'user_name' => $autorizacionesViejas[$i]['user_name'],
                        'pago_id'   => $detalle->id,
                        'fecha'     => $autorizacionesViejas[$i]['fecha'],
                    ]);
                }

                // Add any newly uploaded proof files (one or many)
                $this->guardarComprobantesPago($detalle, $request, $i);

                $conceptoVenta = Concepto::firstOrCreate(['nombre' => 'Venta de unidad']);

                if ($entidad) {
                    if ($entidad->tangible) {
                        // Regla de negocio: una sola caja por sucursal y por día.
                        // Red de seguridad: el control de verdad es faltaCajaAbierta(), en la validación.
                        $cajaAbierta = Caja::abiertaDelDia($request->sucursal_id);

                        if (!$cajaAbierta) {
                            DB::rollBack();
                            return redirect()->back()
                                ->withErrors("No hay caja abierta para esta sucursal. No se puede registrar el pago en efectivo.")
                                ->withInput();
                        }

                        // Cash payment: impacts physical cash register
                        MovimientoCaja::create([
                            'caja_id' => $cajaAbierta->id,
                            'concepto_id' => $conceptoVenta->id,
                            'entidad_id' => $entidad->id,
                            'venta_id' => $venta->id,
                            'tipo' => 'Ingreso',
                            'monto' => $detalle->monto,
                            'acreditado' => 1,
                            'fecha' => now(),
                            'user_id' => $request->user_id,
                            'referencia' => $detalle->detalle,
                        ]);
                    }
                    if ($entidad->cuenta) {
                        // Non-tangible payment: impacts entity account only
                        \App\Models\MovimientoCuenta::create([
                            'entidad_id' => $entidad->id,
                            'tipo' => 'Ingreso',
                            'monto' => $detalle->monto,
                            'fecha' => $detalle->fecha,
                            'concepto' => $conceptoVenta->nombre,
                            'venta_id' => $venta->id,
                            'pago_id' => $detalle->id,
                            'user_id' => $request->user_id,
                        ]);
                    }
                }
            }

        } catch (\Exception $ex) {
            // Incluye QueryException y el faltante de stock de los conceptos:
            // cualquiera de los dos tiene que hacer rollback, nunca un 500.
            $error = $ex->getMessage();
            $ok = 0;
        }

        if ($ok) {
            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'Registro actualizado satisfactoriamente');
        } else {
            DB::rollback();
            return redirect()->back()->with('error', $error);
        }
    }

    public function show($id) {
        $venta = Venta::with('pagos.comprobantes', 'pagos.entidad', 'unidad', 'cliente', 'ventaArticulos.piezas.pieza')->findOrFail($id);
        $users = \App\Models\User::orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        $entidads = Entidad::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('ventas.show', compact('venta', 'users', 'sucursals', 'entidads'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $venta = Venta::with('ventaArticulos.piezas.pieza.tipoPieza')->findOrFail($id);

        // Los conceptos vuelven al stock antes de irse con la venta.
        // La fila de venta_piezas se borra sola por la FK en cascada.
        if ($venta->ventaArticulos) {
            $this->reponerStockArticulos($venta->ventaArticulos->piezas);
        }

        $venta->autorizaciones()->delete();
        // Elimina las relaciones
        $venta->pagos()->delete();


        // Elimina el venta
        $venta->delete();

        return redirect()->route('ventas.index')
            ->with('success','Venta eliminada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function autorizar($id)
    {
        $venta = Venta::findOrFail($id);

        $venta->autorizaciones()->create([
            'user_id'   => auth()->id(),
            'user_name' => auth()->user()->name,
            'fecha'     => Carbon::now()->toDateString(),
        ]);

        return redirect()->route('ventas.index')
            ->with('success', 'Venta autorizada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function desautorizar($id)
    {
        $venta = Venta::findOrFail($id);

        $venta->autorizaciones()->delete();

        return redirect()->route('ventas.index')
            ->with('success', 'Venta desautorizada con éxito');
    }

    public function generateBoleto(Request $request)
    {
        $ventaId = $request->query('venta_id');
        $modo = $request->query('modo', 'junto'); // 'junto' o 'separado'
        $archivosSeleccionados = $request->query('archivos', []); // array de ids

        $venta = Venta::findOrFail($ventaId);

        $parametro = Parametro::where('nombre','boleto_compra_venta')->first();
        $template = 'ventas.boleto';

        $data = [
            'venta' => $venta,
            'fecha' => $venta->fecha,
            'parametro' => $parametro,
        ];

        // Generar PDF del boleto
        $pdf = PDF::loadView($template, $data);
        $pdfPath = public_path('temp/Venta_' . $ventaId . '.pdf');
        $pdf->save($pdfPath);

        // Traer documentos seleccionados habilitados en orden
        $docs = Documento::whereIn('id', $archivosSeleccionados)
            ->where('habilitado', 1)
            ->orderBy('orden')
            ->get();

        $docsPaths = $docs->map(function($d) {
            return public_path($d->path); // ya incluye "files/"
        })->toArray();

        $docsUrls = $docs->map(function($d) {
            return asset($d->path); // ya incluye "files/"
        })->toArray();


        //dd($docsPaths);
        if ($modo === 'junto') {
            $outputPath = public_path('temp/Venta_' . $ventaId . '_completo.pdf');

            $pdf = new Fpdi();

            // Agregar boleto
            $pageCount = $pdf->setSourceFile($pdfPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }

            // Agregar documentos adicionales
            foreach ($docsPaths as $docPath) {
                if (file_exists($docPath)) {
                    $pageCount = $pdf->setSourceFile($docPath);
                    for ($i = 1; $i <= $pageCount; $i++) {
                        $tpl = $pdf->importPage($i);
                        $size = $pdf->getTemplateSize($tpl);
                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($tpl);
                    }
                }
            }

            // Guardar y descargar
            $pdf->Output('F', $outputPath);
            return response()->download($outputPath)->deleteFileAfterSend(true);
        } elseif ($modo === 'separado') {
            // Descargar boleto + documentos como zip
            $zip = new \ZipArchive;
            $zipPath = public_path('temp/Venta_' . $ventaId . '.zip');

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                $zip->addFile($pdfPath, 'Boleto_Venta_' . $ventaId . '.pdf');

                foreach ($docsPaths as $docPath) {
                    if (file_exists($docPath)) {
                        $zip->addFile($docPath, basename($docPath));
                    }
                }

                $zip->close();
            }

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }
    }


    public function generateBoleto_old(Request $request,$attach = false)
    {
        $ventaId = $request->query('venta_id');
        $venta = Venta::find($ventaId);

        $parametro = Parametro::where('nombre','boleto_compra_venta')->first();;

        $template = 'ventas.boleto';
        /*$unidadMovimientos = $ventaPieza->unidadMovimientos()->get();*/


        $data = [
            //'remito' => str_pad($ventaPieza->id,8,'0',STR_PAD_LEFT),
            'venta' => $venta,
            'fecha' => $venta->fecha,
            //'destino' => $destino,
            'parametro' => $parametro,
            /*'piezaVentapiezas' => $ventaPieza->piezas,
            'descripcion' => $descripcion,*/
        ];
        //dd($data);




        $pdf = PDF::loadView($template, $data);

        $pdfPath = 'Venta_' . $ventaId . '.pdf';

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

    public function generateFormulario(Request $request,$attach = false)
    {
        $ventaId = $request->query('venta_id');
        $venta = Venta::find($ventaId);

        $parametro = Parametro::where('nombre','boleto_compra_venta')->first();;

        $template = 'ventas.formulario';
        /*$unidadMovimientos = $ventaPieza->unidadMovimientos()->get();*/


        $data = [
            //'remito' => str_pad($ventaPieza->id,8,'0',STR_PAD_LEFT),
            'venta' => $venta,
            'fecha' => $venta->fecha,
            //'destino' => $destino,
            'parametro' => $parametro,
            /*'piezaVentapiezas' => $ventaPieza->piezas,
            'descripcion' => $descripcion,*/
        ];
        //dd($data);




        $pdf = PDF::loadView($template, $data);

        $pdfPath = 'Venta_' . $ventaId . '.pdf';

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
        $columnas = [
            'ventas.fecha',
            'clientes.nombre',
            'unidads.motor',
            'modelos.nombre',
            DB::raw("IFNULL(users.name, ventas.user_name)"),
            'sucursals.nombre',
            DB::raw($this->autorizacionCase()),
        ];
        $busqueda = $request->search;
        $user_id = $request->user_id;
        $sucursal_id = $request->sucursdal_id;
        $fechaDesde = $request->desde;
        $fechaHasta = $request->hasta;

        $sucursalNombre = ($sucursal_id && $sucursal_id != -1)
            ? (Sucursal::find($sucursal_id)->nombre ?? '—')
            : 'Todas';

        $userNombre = ($user_id && $user_id != -1)
            ? (User::find($user_id)->nombre ?? '—')
            : 'Todos';

        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = Venta::select(
            'ventas.id as id',
            'ventas.fecha',
            'clientes.nombre as cliente',
            'unidads.motor',
            'modelos.nombre as modelo',
            DB::raw("IFNULL(users.name, ventas.user_name) as usuario_nombre"),
            'sucursals.nombre as sucursal_nombre',
            DB::raw($this->autorizacionCase('autorizacion'))
        )
            ->leftJoin('sucursals', 'ventas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->leftJoin('unidads', 'ventas.unidad_id', '=', 'unidads.id')
            ->leftJoin('productos', 'unidads.producto_id', '=', 'productos.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('users', 'ventas.user_id', '=', 'users.id');


        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('ventas.sucursal_id', $sucursal_id);
        }


        if (!empty($user_id) && $user_id != '-1') {
            $query->where('ventas.user_id', $user_id);
        }


        if (!empty($fechaDesde)) {
            $query->whereDate('ventas.fecha', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('ventas.fecha', '<=', $fechaHasta);
        }

        // Aplicar búsqueda
        if (!empty($busqueda)) {
            $query->where(function ($query) use ($columnas, $busqueda) {
                foreach ($columnas as $columna) {
                    if ($columna) {
                        $query->orWhere($columna, 'like', "%$busqueda%");
                    }
                }
            });
        }

        $ventas = $query->get();

        // ===============================
        //     📄 CREAR ARCHIVO XLSX
        // ===============================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Ventas");

        // ------------------------------
        // FILTROS
        // ------------------------------

        $sheet->setCellValue('A1', 'Sucursal:');
        $sheet->setCellValue('B1', $sucursalNombre);

        $sheet->setCellValue('A2', 'Vendedor:');
        $sheet->setCellValue('B2', $userNombre);

        $sheet->setCellValue('A3', 'Desde:');
        $sheet->setCellValue('B3', $fechaDesde
            ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y')
            : '—');

        $sheet->setCellValue('A4', 'Hasta:');
        $sheet->setCellValue('B4', $fechaHasta
            ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y')
            : '—');


        $sheet->setCellValue('A5', 'Búsqueda:');
        $sheet->setCellValue('B5', $busqueda ?: '—');

        // Espacio antes de la tabla
        $startRow = 5;

        // ------------------------------
        // ENCABEZADOS DE LA TABLA
        // ------------------------------
        $headers = [
           "Fecha", "Cliente", "Nro. Motor", "Modelo", "Vendedor",
             "Sucursal", "Estado"
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

        foreach ($ventas as $p) {

            $sheet->setCellValue("A{$row}",
                $p->fecha
                    ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y')
                    : '—'
            );
            $sheet->setCellValue("B{$row}", $p->cliente);
            $sheet->setCellValue("C{$row}", $p->motor);
            $sheet->setCellValue("D{$row}", $p->modelo);
            $sheet->setCellValue("E{$row}", $p->usuario_nombre);
            $sheet->setCellValue("F{$row}", $p->sucursal_nombre);
            $sheet->setCellValue("G{$row}", $p->autorizacion);


            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "ventas.xlsx";
        $filePath = tempnam(sys_get_temp_dir(), $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }






    public function exportarPDF(Request $request)
    {
        ini_set('memory_limit', '-1'); // ilimitado
        ini_set('max_execution_time', 0);

        $columnas = [
            'ventas.fecha',
            'clientes.nombre',
            'unidads.motor',
            'modelos.nombre',
            DB::raw("IFNULL(users.name, ventas.user_name)"),
            'sucursals.nombre',
            DB::raw($this->autorizacionCase())
        ];

        $busqueda = $request->search;
        $user_id = $request->user_id;
        $sucursal_id = $request->sucursdal_id;
        $fechaDesde = $request->desde;
        $fechaHasta = $request->hasta;


        $sucursalNombre = ($sucursal_id && $sucursal_id != -1)
            ? (Sucursal::find($sucursal_id)->nombre ?? '—')
            : 'Todas';

        // ===============================
        //     NOMBRE DEL USUARIO
        // ===============================
        $usuarioFiltrado = "Todos";

        if (!empty($user_id) && $user_id != "-1") {
            $usuario = User::find($user_id);
            if ($usuario) {
                $usuarioFiltrado = $usuario->name;
            } else {
                $usuarioFiltrado = "Desconocido";
            }
        }


        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = Venta::select(
            'ventas.id as id',
            'ventas.fecha',
            'clientes.nombre as cliente',
            'unidads.motor',
            'modelos.nombre as modelo',
            DB::raw("IFNULL(users.name, ventas.user_name) as usuario_nombre"),
            'sucursals.nombre as sucursal_nombre',
            DB::raw($this->autorizacionCase('autorizacion'))
        )
            ->leftJoin('sucursals', 'ventas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->leftJoin('unidads', 'ventas.unidad_id', '=', 'unidads.id')
            ->leftJoin('productos', 'unidads.producto_id', '=', 'productos.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('users', 'ventas.user_id', '=', 'users.id');


        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('ventas.sucursal_id', $sucursal_id);
        }


        if (!empty($user_id) && $user_id != '-1') {
            $query->where('ventas.user_id', $user_id);
        }


        if (!empty($fechaDesde)) {
            $query->whereDate('ventas.fecha', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('ventas.fecha', '<=', $fechaHasta);
        }

        // Aplicar búsqueda
        if (!empty($busqueda)) {
            $query->where(function ($query) use ($columnas, $busqueda) {
                foreach ($columnas as $columna) {
                    if ($columna) {
                        $query->orWhere($columna, 'like', "%$busqueda%");
                    }
                }
            });
        }

        $ventas = $query->get();

        // Pasamos datos a la vista PDF
        $data = [
            'ventas' => $ventas,
            'busqueda' => $busqueda,
            'usuarioFiltrado' => $usuarioFiltrado,
            'sucursalNombre' => $sucursalNombre,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ];

        $pdf = PDF::loadView('ventas.exportpdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('ventas.exportpdf');
    }

}
