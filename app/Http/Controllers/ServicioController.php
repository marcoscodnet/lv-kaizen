<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Sucursal;
use App\Models\Servicio;
use App\Models\Venta;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'servicios.carga',
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
            'servicios.carga',
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
            $query->whereDate('servicios.carga', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('servicios.carga', '<=', $fechaHasta);
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

    public function show($id) {

    }
}
