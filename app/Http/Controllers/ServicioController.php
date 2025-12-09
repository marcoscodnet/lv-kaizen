<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;


use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\Servicio;
use App\Models\Unidad;
use App\Models\Provincia;
use App\Models\TipoServicio;
use App\Models\User;
use App\Models\Venta;
use App\Traits\SanitizesInput;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDF;
use App\Constants;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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


        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('servicios.sucursal_id', $sucursal_id);
        }



        if (!empty($user_id) && $user_id != '-1') {
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

        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $tipos = TipoServicio::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('servicios.registrar', compact('sucursals', 'venta','provincias','tipos'));
    }

    public function store(Request $request)
    {
        //dd($request->all());


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
            $input['carga'] = now();
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

    public function edit($id=null)
    {
        $servicio = Servicio::find($id);


        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $tipos = TipoServicio::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('servicios.edit', compact('sucursals', 'servicio','provincias','tipos'));
    }

    public function update(Request $request, $id)
    {

        $servicio = Servicio::find($id);

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
            //$input['user_id'] = auth()->id(); // o auth()->user()->id
            $servicio->update($input);

        }catch(QueryException $ex){
            $error = $ex->getMessage();
            $ok=0;

        }
        if ($ok){
            DB::commit();
            $respuestaID='success';
            $respuestaMSJ='Registro modificado satisfactoriamente';
        }
        else{
            DB::rollback();
            $respuestaID='error';
            $respuestaMSJ=$error;
        }

        return redirect()->route('servicios.index')->with($respuestaID,$respuestaMSJ);



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Servicio::find($id)->delete();

        return redirect()->route('servicios.index')
            ->with('success','Servicio eliminado con éxito');
    }

    public function show($id=null)
    {
        $servicio = Servicio::find($id);


        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        $tipos = TipoServicio::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('servicios.show', compact('sucursals', 'servicio','tipos'));
    }

    public function generatePDF(Request $request,$attach = false)
    {
        $servicioId = $request->query('servicio_id');
        $servicio = Servicio::find($servicioId);

        $template = 'servicios.pdf';

        $motor = $servicio->motor;

        $marcaId = Unidad::where('motor', $motor)
            ->join('productos', 'unidads.producto_id', '=', 'productos.id')
            ->value('productos.marca_id');
        $esHonda=0;
        if ($marcaId == Constants::ID_HONDA) {
            $esHonda=1;
        }
        $data = [
            'servicio' => $servicio,
            'esHonda' => $esHonda
        ];
        //dd($data);
        $pdf = PDF::loadView($template, $data);

        $pdfPath = 'R 270 Orden de servicio ' . $servicioId . '.pdf';

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


        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('servicios.sucursal_id', $sucursal_id);
        }



        if (!empty($user_id) && $user_id != '-1') {
            $query->where('servicios.user_id', $user_id);
        }


        if (!empty($fechaDesde)) {
            $query->whereDate('servicios.carga', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('servicios.carga', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $servicios = $query->get();

        // ===============================
        //     📄 CREAR ARCHIVO XLSX
        // ===============================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Servicios");

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
            "Nro.", "Fecha", "Nro. Motor", "Modelo",
            "Chasis", "Cliente", "Técnico", "Monto", "Servicio", "Cerrado", "Sucursal", "Vendedor"
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

        foreach ($servicios as $p) {
            $sheet->setCellValue("A{$row}", $p->id);
            $sheet->setCellValue("B{$row}",
                $p->carga
                    ? \Carbon\Carbon::parse($p->carga)->format('d/m/Y')
                    : '—'
            );
            $sheet->setCellValue("C{$row}", $p->motor);
            $sheet->setCellValue("D{$row}", $p->modelo);
            $sheet->setCellValue("E{$row}", $p->chasis);
            $sheet->setCellValue("F{$row}", $p->cliente);
            $sheet->setCellValue("G{$row}", $p->mecanicos);
            $sheet->setCellValue("H{$row}", $p->monto);
            $sheet->setCellValue("I{$row}", $p->tipo_servicio);
            $sheet->setCellValue("J{$row}", $p->pagado);
            $sheet->setCellValue("K{$row}", $p->sucursal_nombre);
            $sheet->setCellValue("L{$row}", $p->usuario_nombre);
            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "servicios.xlsx";
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


        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('servicios.sucursal_id', $sucursal_id);
        }



        if (!empty($user_id) && $user_id != '-1') {
            $query->where('servicios.user_id', $user_id);
        }


        if (!empty($fechaDesde)) {
            $query->whereDate('servicios.carga', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('servicios.carga', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $servicios = $query->get();

        // Pasamos datos a la vista PDF
        $data = [
            'servicios' => $servicios,
            'busqueda' => $busqueda,
            'usuarioFiltrado' => $usuarioFiltrado,
            'sucursalNombre' => $sucursalNombre,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ];

        $pdf = PDF::loadView('servicios.exportpdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('servicios.exportpdf');
    }
}
