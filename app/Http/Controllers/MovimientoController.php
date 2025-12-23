<?php

namespace App\Http\Controllers;


use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Movimiento;
use App\Models\Unidad;
use App\Models\User;
use App\Models\UnidadMovimiento;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MovimientoController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:unidad-movimiento-listar|unidad-movimiento-crear|unidad-movimiento-editar|unidad-movimiento-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:unidad-movimiento-crear', ['only' => ['create','store']]);
        $this->middleware('permission:unidad-movimiento-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:unidad-movimiento-eliminar', ['only' => ['destroy']]);
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
        $movimientos = Movimiento::all();
        return view ('movimientos.index',compact('movimientos','users'));
    }


    public function dataTable(Request $request)
    {
        $busqueda = $request->input('search.value');
        $user_id = $request->input('user_id');

        $columnsMap = [
            'usuario_nombre',
            'origen_nombre',
            'destino_nombre',
            'fecha',
            'cuadros',
            'motores',
            'estado_orden', // 👈 esta manda el orden
            'estado',
            'id'
        ];

        $colIndex = $request->input('order.0.column', 0);
        $dir = $request->input('order.0.dir', 'asc');
        $sortColumn = $columnsMap[$colIndex] ?? 'estado';

        $datos = $this->obtenerMovimientosFiltrados($busqueda, $user_id, $sortColumn, $dir);

        $recordsFiltered = $datos->count();
        $recordsTotal = Movimiento::count();

        // PAGINAR
        $datos = $datos->slice($request->start)->take($request->length)->values();

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


        $productos = Producto::with(['tipoUnidad', 'marca', 'modelo', 'color'])
            ->get()
            ->mapWithKeys(function ($producto) {
                $texto = ($producto->tipoUnidad->nombre ?? '') . ' - '
                    . ($producto->marca->nombre ?? '') . ' - '
                    . ($producto->modelo->nombre ?? '') . ' - '
                    . ($producto->color->nombre ?? '');

                return [$producto->id => $texto];
            })
            ->prepend('', ''); // si necesitas un vacío al principio
        $origens = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $destinos = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('movimientos.create', compact('productos','origens','destinos'));
    }

    public function store(Request $request)
    {
        $rules = [
            'sucursal_origen_id' => 'required',
            'sucursal_destino_id' => 'required',
            'fecha' => 'required|date',
            'unidad_id' => 'required|array|min:1',
            'unidad_id.*' => 'required|distinct',
        ];


        // Definir los mensajes de error personalizados
        $messages = [
            'sucursal_origen_id.required' => 'El campo Origen es obligatorio.',
            'sucursal_destino_id.required' => 'El campo Destino es obligatorio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'unidad_id.required' => 'Debe agregar al menos una unidad.',
            'unidad_id.min' => 'Debe agregar al menos una unidad.',
            'unidad_id.*.required' => 'Debe seleccionar una unidad para cada producto.',
            'unidad_id.*.distinct' => 'No puede repetir unidades.',
        ];


        // Crear el validador con las reglas y mensajes
        $validator = Validator::make($request->all(), $rules, $messages);

        // Validar y verificar si hay errores
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $input = $this->sanitizeInput($request->all());
        // Obtener el ID del usuario autenticado
        $userId = Auth::id();
        $input['user_id'] = $userId;

        DB::beginTransaction();
        $ok=1;
        try {
            $movimiento = Movimiento::create($input);

            $lastid=$movimiento->id;
            if(count($request->unidad_id) > 0)
            {
                foreach($request->unidad_id as $item=>$v){

                    $data2=array(
                        'movimiento_id'=>$lastid,
                        'unidad_id'=>$request->unidad_id[$item]
                    );
                    try {
                        UnidadMovimiento::create($data2);

                    }catch(QueryException $ex){
                        $error = $ex->getMessage();
                        $ok=0;
                        continue;
                    }
                }
            }

        }catch(Exception $e){
            //if email or phone exist before in db redirect with error messages
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

        return redirect()->route('movimientos.index')->with($respuestaID,$respuestaMSJ);



    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $movimiento = Movimiento::findOrFail($id);

            // Obtenés la sucursal origen desde el movimiento
            $sucursalOrigen = $movimiento->sucursal_origen_id;

            // Revertir todas las unidades que participaron en el movimiento
            foreach ($movimiento->unidadMovimientos as $um) {
                // Revertir la unidad a la sucursal original
                Unidad::where('id', $um->unidad_id)->update([
                    'sucursal_id' => $sucursalOrigen
                ]);

                // Eliminar el registro intermedio
                $um->delete();
            }

            // Finalmente, eliminar el movimiento
            $movimiento->delete();

            DB::commit();
            return redirect()->route('movimientos.index')->with('success', 'Movimiento eliminado y unidades revertidas a su sucursal original.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar movimiento: ' . $e->getMessage());
        }
    }

    public function generatePDF(Request $request,$attach = false)
    {
        $movimientoId = $request->query('movimiento_id');
        $movimiento = Movimiento::find($movimientoId);



        $template = 'movimientos.pdf';
        $unidadMovimientos = $movimiento->unidadMovimientos()->get();



        $data = [
            'remito' => $movimientoId,
            'fecha' => $movimiento->fecha,
            'origen' => $movimiento->sucursalOrigen,
            'destino' => $movimiento->sucursalDestino,
            'unidades' => $unidadMovimientos,
        ];
        //dd($data);




        $pdf = PDF::loadView($template, $data);

        $pdfPath = 'Movimiento_' . $movimientoId . '.pdf';

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

    public function show($id)
    {
        $movimiento = Movimiento::find($id);

        $users = \App\Models\User::orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $origens = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $destinos = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('movimientos.show', compact('movimiento','origens','destinos','users'));
    }

    private function obtenerMovimientosFiltrados($busqueda, $user_id, $sortColumn, $dir)
    {
        $movimientosQuery = Movimiento::with(['unidadMovimientos.unidad'])
            ->leftJoin('sucursals as origen', 'movimientos.sucursal_origen_id', '=', 'origen.id')
            ->leftJoin('sucursals as destino', 'movimientos.sucursal_destino_id', '=', 'destino.id')
            ->leftJoin('users', 'movimientos.user_id', '=', 'users.id')
            ->leftJoin('users as acepta', 'movimientos.user_acepta_id', '=', 'acepta.id')
            ->select(
                'movimientos.*',
                DB::raw("IFNULL(users.name, movimientos.user_name) as usuario_nombre"),
                'acepta.name as acepta_nombre',
                'origen.nombre as origen_nombre',
                'destino.nombre as destino_nombre',
                'movimientos.fecha'
            );

        // FILTRO POR USUARIO
        if (!empty($user_id) && $user_id != '-1') {
            $movimientosQuery->where('movimientos.user_id', $user_id);
        }

        $movimientos = $movimientosQuery->get();

        // MAPEO + CONCAT
        $datos = $movimientos->map(function ($movimiento) {
            // =========================
            // TEXTO DE ESTADO
            // =========================
            $estadoTexto = ucfirst(strtolower($movimiento->estado));

            if ($movimiento->estado === 'Aceptado' && $movimiento->user_acepta) {
                $fecha = \Carbon\Carbon::parse($movimiento->aceptado)
                    ->format('d/m/Y');

                $estadoTexto = "Aceptado ({$movimiento->acepta_nombre} {$fecha})";
            }
            $estadoOrden = $movimiento->estado === 'Pendiente' ? 1 : 0;
            return [
                'id' => $movimiento->id,
                'sucursal_destino_id' => $movimiento->sucursal_destino_id,
                'estado' => $movimiento->estado,
                'estado_texto' => $estadoTexto,
                'estado_orden' => $estadoOrden,   // 👈 NUEVO
                'usuario_nombre' => $movimiento->usuario_nombre,
                'origen_nombre' => $movimiento->origen_nombre,
                'destino_nombre' => $movimiento->destino_nombre,
                'fecha' => $movimiento->fecha,
                'cuadros' => $movimiento->unidadMovimientos->pluck('unidad.cuadro')->filter()->implode(', '),
                'motores' => $movimiento->unidadMovimientos->pluck('unidad.motor')->filter()->implode(', ')
            ];
        });

        // BUSQUEDA
        if (!empty($busqueda)) {
            $busqueda = mb_strtolower($busqueda);

            $datos = $datos->filter(function ($item) use ($busqueda) {
                return str_contains(mb_strtolower($item['usuario_nombre']), $busqueda)
                    || str_contains(mb_strtolower($item['origen_nombre']), $busqueda)
                    || str_contains(mb_strtolower($item['destino_nombre']), $busqueda)
                    || str_contains(mb_strtolower($item['fecha']), $busqueda)
                    || str_contains(mb_strtolower($item['cuadros']), $busqueda)
                    || str_contains(mb_strtolower($item['estado']), $busqueda)
                    || str_contains(mb_strtolower($item['motores']), $busqueda);
            });
        }

        // ORDEN
        $datos = $dir === 'asc'
            ? $datos->sortBy($sortColumn)
            : $datos->sortByDesc($sortColumn);

        return $datos->values();
    }


    public function exportarXLS(Request $request)
    {
        $busqueda = $request->search;
        $user_id = $request->user_id;

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

        // MISMAS COLUMNAS Y ORDEN:
        $sortColumn = 'id';
        $dir = 'asc';

        $datos = $this->obtenerMovimientosFiltrados($busqueda, $user_id, $sortColumn, $dir);

        // Crear Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle("Movimientos");

        // FILTROS
        $sheet->setCellValue('A1', 'Filtros aplicados');
        $sheet->setCellValue('A2', 'Búsqueda:');
        $sheet->setCellValue('B2', $busqueda ?: 'Todos');

        $sheet->setCellValue('A3', 'Usuario:');
        $sheet->setCellValue('B3', $usuarioFiltrado ?: 'Todos');

        // ENCABEZADOS
        $headers = [
            "Usuario", "Origen", "Destino", "Fecha", "Cuadros", "Motores"
        ];

        $row = 5;
        $col = 1;

        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($col, $row, $h);
            $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
            $col++;
        }

        $row++;

        // DATOS
        foreach ($datos as $m) {
            $sheet->setCellValue("A$row", $m['usuario_nombre']);
            $sheet->setCellValue("B$row", $m['origen_nombre']);
            $sheet->setCellValue("C$row", $m['destino_nombre']);
            $sheet->setCellValue("G{$row}",
                $m['fecha']
                    ? \Carbon\Carbon::parse($m['fecha'])->format('d/m/Y')
                    : '—'
            );
            $sheet->setCellValue("E$row", $m['cuadros']);
            $sheet->setCellValue("F$row", $m['motores']);
            $row++;
        }

        // AUTO SIZE
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // EXPORTAR
        $fileName = "movimientos_unidades.xlsx";
        $filePath = tempnam(sys_get_temp_dir(), $fileName);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }



    public function exportarPDF(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $busqueda = $request->search;
        $user_id = $request->user_id;

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

        // ===============================
        // MISMA QUERY QUE dataTable()
        // ===============================
        $movimientosQuery = Movimiento::with(['unidadMovimientos.unidad'])
            ->leftJoin('sucursals as origen', 'movimientos.sucursal_origen_id', '=', 'origen.id')
            ->leftJoin('sucursals as destino', 'movimientos.sucursal_destino_id', '=', 'destino.id')
            ->leftJoin('users', 'movimientos.user_id', '=', 'users.id')
            ->select(
                'movimientos.id as id',
                DB::raw("IFNULL(users.name, movimientos.user_name) as usuario_nombre"),
                'origen.nombre as origen_nombre',
                'destino.nombre as destino_nombre',
                'movimientos.fecha'
            );

        // FILTRAR POR USUARIO
        if (!empty($user_id) && $user_id != '-1') {
            $movimientosQuery->where('movimientos.user_id', $user_id);
        }

        $movimientos = $movimientosQuery->get();

        // ===============================
        // MAPEO EXACTO AL DATATABLE
        // ===============================
        $datos = $movimientos->map(function($movimiento) {
            return [
                'id' => $movimiento->id,
                'usuario_nombre' => $movimiento->usuario_nombre,
                'origen_nombre' => $movimiento->origen_nombre,
                'destino_nombre' => $movimiento->destino_nombre,
                'fecha' => $movimiento->fecha
                    ? \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y')
                    : '',
                'cuadros' => $movimiento->unidadMovimientos->pluck('unidad.cuadro')->filter()->implode(', '),
                'motores' => $movimiento->unidadMovimientos->pluck('unidad.motor')->filter()->implode(', ')
            ];
        });

        // ===============================
        // FILTRO DE BÚSQUEDA
        // ===============================
        if (!empty($busqueda)) {
            $busqueda = mb_strtolower($busqueda);
            $datos = $datos->filter(function($item) use ($busqueda) {
                return str_contains(mb_strtolower($item['usuario_nombre']), $busqueda)
                    || str_contains(mb_strtolower($item['origen_nombre']), $busqueda)
                    || str_contains(mb_strtolower($item['destino_nombre']), $busqueda)
                    || str_contains(mb_strtolower($item['fecha']), $busqueda)
                    || str_contains(mb_strtolower($item['cuadros']), $busqueda)
                    || str_contains(mb_strtolower($item['motores']), $busqueda);
            });
        }

        // ===============================
        // ENVIAR TODO AL PDF
        // ===============================
        $data = [
            'movimientos' => $datos,
            'busqueda' => $busqueda,
            'usuarioFiltrado' => $usuarioFiltrado
        ];

        $pdf = PDF::loadView('movimientos.exportpdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('movimientos.exportpdf');
    }


    public function aceptar(Movimiento $movimiento)
    {
        if ($movimiento->estado !== 'Pendiente') {
            return back()->with('error', 'El movimiento ya fue procesado');
        }

        DB::transaction(function () use ($movimiento) {

            foreach ($movimiento->unidadMovimientos as $um) {
                Unidad::where('id', $um->unidad_id)
                    ->update([
                        'sucursal_id' => $movimiento->sucursal_destino_id
                    ]);
            }

            $movimiento->update([
                'estado'       => 'Aceptado',
                'aceptado_por' => auth()->id(),
                'aceptado_at'  => now(),
            ]);
        });

        return back()->with('success', 'Movimiento aceptado correctamente');
    }





}
