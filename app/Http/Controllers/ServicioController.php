<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;


use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\Servicio;
use App\Models\Provincia;
use App\Models\TipoServicio;
use App\Models\Venta;
use App\Traits\SanitizesInput;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServicioController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:servicio-listar|servicio-crear|servicio-editar|servicio-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:servicio-crear', ['only' => ['create','store']]);
        $this->middleware('permission:servicio-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:servicio-eliminar', ['only' => ['destroy']]);
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
        $servicios = Servicio::all();

        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1');
        return view ('servicios.index',compact('servicios','users','sucursals'));
    }


    public function dataTable(Request $request)
    {
        $columnas = [
            'servicios.id',
            'servicios.id',
            'servicios.ingreso',
            'servicios.motor',
            'servicios.modelo',
            'servicios.chasis',
            'clientes.nombre',
            'servicios.mecanicos',
            'servicios.monto',
            'tipo_servicios.nombre',
            DB::raw("CASE WHEN servicios.pagado = 1 THEN 'SI' ELSE 'NO' END"),
            'sucursals.nombre',
            'users.name'
        ];

        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');
        $user_id = $request->input('user_id');
        $sucursal_id = $request->input('sucursal_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        // Query base
        $query = Servicio::select(
            'servicios.id as id',
            'servicios.id as nro',
            'servicios.ingreso',
            'servicios.motor',
            'servicios.modelo',
            'servicios.chasis',
            'clientes.nombre as cliente',
            'servicios.mecanicos',
            'servicios.monto',
            'tipo_servicios.nombre as tipo_servicio',
            DB::raw("CASE WHEN servicios.pagado = 1 THEN 'SI' ELSE 'NO' END as pagado"),
            'sucursals.nombre as sucursal_nombre',
            'users.name as usuario_nombre'

        )
            ->leftJoin('tipo_servicios', 'servicios.tipo_servicio_id', '=', 'tipo_servicios.id')
            ->leftJoin('sucursals', 'servicios.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('clientes', 'servicios.cliente_id', '=', 'clientes.id')
            ->leftJoin('users', 'servicios.user_id', '=', 'users.id');


        if (!empty($sucursal_id)) {

            $request->session()->put('sucursal_filtro_servicio', $sucursal_id);

        }
        else{
            $sucursal_id = $request->session()->get('sucursal_filtro_servicio');

        }
        if ($sucursal_id=='-1'){
            $request->session()->forget('sucursal_filtro_servicio');
            $sucursal_id='';
        }
        if (!empty($sucursal_id)) {

            $query->where('servicios.sucursal_id', $sucursal_id);


        }


        if (!empty($user_id)) {

            $request->session()->put('user_filtro_servicio', $user_id);

        }
        else{
            $user_id = $request->session()->get('user_filtro_servicio');

        }
        if ($user_id=='-1'){
            $request->session()->forget('user_filtro_servicio');
            $user_id='';
        }
        if (!empty($user_id)) {

            $query->where('servicios.user_id', $user_id);


        }


        if (!empty($fechaDesde)) {
            $query->whereDate('servicios.ingreso', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('servicios.ingreso', '<=', $fechaHasta);
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
        $totalServicios = (clone $baseQuery)->count();

        // Suma del monto directamente desde la tabla servicios
        $totalServiciosImporte = (clone $baseQuery)->sum('servicios.monto');

        // Cantidad filtrada
        $recordsFiltered = (clone $baseQuery)->count();

        // Datos paginados
        $datos = (clone $baseQuery)
            ->orderBy($columnaOrden, $orden)
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Total sin filtros
        $recordsTotal = Servicio::count();

        return response()->json([
            'data' => $datos,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
            'totales' => [
                'totalServicios' => $totalServicios,
                'totalServiciosImporte' => $totalServiciosImporte
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

        $users = \App\Models\User::orderBy('name')
            ->pluck('name', 'id')
            ->prepend('Todos', '-1');
        $ventas = Venta::all();

        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1');
        return view ('servicios.unidads',compact('ventas','users','sucursals'));
    }


    public function unidadDataTable(Request $request)
    {
        $columnas = [
            'unidads.motor',
            'modelos.nombre',
            'clientes.nombre',
            DB::raw("IFNULL(users.name, ventas.user_name)"),
            'sucursals.nombre',
            'ventas.fecha',
            DB::raw("CASE WHEN autorizacions.id IS NOT NULL THEN 'Autorizada' ELSE 'No autorizada' END"),
            'ventas.forma'
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
            'unidads.motor',
            'modelos.nombre as modelo',
            'clientes.nombre as cliente',
            DB::raw("IFNULL(users.name, ventas.user_name) as usuario_nombre"),
            'sucursals.nombre as sucursal_nombre',
            'ventas.fecha',
            DB::raw("CASE WHEN autorizacions.id IS NOT NULL THEN 'Autorizada' ELSE 'No autorizada' END as autorizacion"),
            'ventas.forma'
        )
            ->leftJoin('sucursals', 'ventas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->leftJoin('unidads', 'ventas.unidad_id', '=', 'unidads.id')
            ->leftJoin('productos', 'unidads.producto_id', '=', 'productos.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('users', 'ventas.user_id', '=', 'users.id')
            ->leftJoin('autorizacions', 'autorizacions.unidad_id', '=', 'unidads.id');

        if (!empty($sucursal_id)) {

            $request->session()->put('sucursal_filtro_venta', $sucursal_id);

        }
        else{
            $sucursal_id = $request->session()->get('sucursal_filtro_venta');

        }
        if ($sucursal_id=='-1'){
            $request->session()->forget('sucursal_filtro_venta');
            $sucursal_id='';
        }
        if (!empty($sucursal_id)) {

            $query->where('ventas.sucursal_id', $sucursal_id);


        }


        if (!empty($user_id)) {

            $request->session()->put('user_filtro_venta', $user_id);

        }
        else{
            $user_id = $request->session()->get('user_filtro_venta');

        }
        if ($user_id=='-1'){
            $request->session()->forget('user_filtro_venta');
            $user_id='';
        }
        if (!empty($user_id)) {

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




        // Obtener la cantidad total de registros después de aplicar el filtro de búsqueda
        $recordsFiltered = $query->count();


        $datos = $query->orderBy($columnaOrden, $orden)->skip($request->input('start'))->take($request->input('length'))->get();

        // Obtener la cantidad total de registros sin filtrar
        $recordsTotal = Venta::count();



        return response()->json([
            'data' => $datos, // Obtener solo los elementos paginados
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
        ]);
    }

    public function registrar($id=null)
    {
        $venta = Venta::find($id);
        /*$users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');*/

        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $tipos = TipoServicio::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('servicios.registrar', compact('sucursals', 'venta','provincias','tipos'));
    }

    public function store(Request $request)
    {
        //dd($request->all());

        $precioSugerido = $request->input('precio', 0);

// Sumamos los montos ingresados
        $totalMonto = $request->input('totalMonto', 0);
        $totalAcreditado = $request->input('totalAcreditado', 0);


        $rules = [
            'venta' => 'nullable|date',
            'modelo' => 'required',
            'motor' => 'required',
            'chasis' => 'required',
            'year' => 'required',
            'cliente_id' => 'required',
            'sucursal_id' => 'required',
            'kilometros' => 'required',
            'tipo_servicio_id' => 'required',
            'ingreso' => 'required|date_format:d/m/Y H:i:s',
            'entrega' => 'required|date',
        ];


        // Definir los mensajes de error personalizados
        $messages = [

            'year.required' => 'El año es obligatorio.',
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'tipo_servicio_id.required' => 'Debe seleccionar un tipo.',
            'ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'ingreso.date_format' => 'La fecha de ingreso no coincide con el formato d/m/Y H:i:s.',
            'entrega.required' => 'La fecha de compromiso entrega es obligatoria.',
        ];



        // Crear el validador con las reglas y mensajes
        $validator = Validator::make($request->all(), $rules, $messages);


        // Validar y verificar si hay errores
        if ($validator->fails()) {
            $cliente = Cliente::find($request->input('cliente_id'));
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->all() + [
                        'cliente_nombre' => optional($cliente)->full_name_phone, // 👈 tu accessor
                    ]);
        }


        $input = $this->sanitizeInput($request->all());


        DB::beginTransaction();
        $ok=1;
        try {
            $input['ingreso']=$request->filled('ingreso')
                ? Carbon::createFromFormat('d/m/Y H:i:s', $request->ingreso)->format('Y-m-d H:i:s')
                : null;
            // Asignar el usuario logueado
            $input['user_id'] = auth()->id(); // o auth()->user()->id
            $servicio = Servicio::create($input);

        }catch(QueryException $ex){
            $error = $ex->getMessage();
            $ok=0;

        }
        if ($ok){
            DB::commit();
            $respuestaID='success';
            $respuestaMSJ='Registro creado satisfactoriamente';
        }
        else{
            DB::rollback();
            $respuestaID='error';
            $respuestaMSJ=$error;
        }

        return redirect()->route('servicios.index')->with($respuestaID,$respuestaMSJ);



    }


}
