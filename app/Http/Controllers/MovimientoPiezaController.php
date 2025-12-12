<?php

namespace App\Http\Controllers;

use App\Models\MovimientoPieza;

use App\Models\Sucursal;
use App\Models\Pieza;
use App\Models\PiezaMovimiento;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MovimientoPiezaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:pieza-movimiento-listar|pieza-movimiento-crear|pieza-movimiento-editar|pieza-movimiento-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:pieza-movimiento-crear', ['only' => ['create','store']]);
        $this->middleware('permission:pieza-movimiento-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:pieza-movimiento-eliminar', ['only' => ['destroy']]);
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
        $movimientos = MovimientoPieza::all();
        return view ('movimientoPiezas.index',compact('movimientos','users'));
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
            'id',
            'cuadros',
            'motores',
            'id'
        ];

        $colIndex = $request->input('order.0.column', 0);
        $dir = $request->input('order.0.dir', 'asc');
        $sortColumn = $columnsMap[$colIndex] ?? 'id';

        $datos = $this->obtenerMovimientosFiltrados($busqueda, $user_id, $sortColumn, $dir);

        $recordsFiltered = $datos->count();
        $recordsTotal = MovimientoPieza::count();

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


        $productos = Producto::with(['tipoPieza', 'marca', 'modelo', 'color'])
            ->get()
            ->mapWithKeys(function ($producto) {
                $texto = ($producto->tipoPieza->nombre ?? '') . ' - '
                    . ($producto->marca->nombre ?? '') . ' - '
                    . ($producto->modelo->nombre ?? '') . ' - '
                    . ($producto->color->nombre ?? '');

                return [$producto->id => $texto];
            })
            ->prepend('', ''); // si necesitas un vacío al principio
        $origens = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $destinos = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('movimientoPiezas.create', compact('productos','origens','destinos'));
    }

    public function store(Request $request)
    {
        $rules = [
            'sucursal_origen_id' => 'required',
            'sucursal_destino_id' => 'required',
            'fecha' => 'required|date',
            'pieza_id' => 'required|array|min:1',
            'pieza_id.*' => 'required|distinct',
        ];


        // Definir los mensajes de error personalizados
        $messages = [
            'sucursal_origen_id.required' => 'El campo Origen es obligatorio.',
            'sucursal_destino_id.required' => 'El campo Destino es obligatorio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'pieza_id.required' => 'Debe agregar al menos una pieza.',
            'pieza_id.min' => 'Debe agregar al menos una pieza.',
            'pieza_id.*.required' => 'Debe seleccionar una pieza para cada producto.',
            'pieza_id.*.distinct' => 'No puede repetir piezas.',
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
            $movimiento = MovimientoPieza::create($input);

            $lastid=$movimiento->id;
            if(count($request->pieza_id) > 0)
            {
                foreach($request->pieza_id as $item=>$v){

                    $data2=array(
                        'movimientoPieza_id'=>$lastid,
                        'pieza_id'=>$request->pieza_id[$item]
                    );
                    try {
                        PiezaMovimiento::create($data2);

                        // Actualizar sucursal_id de la pieza
                        Pieza::where('id', $request->pieza_id[$item])
                            ->update(['sucursal_id' => $request->sucursal_destino_id]);

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

        return redirect()->route('movimientoPiezas.index')->with($respuestaID,$respuestaMSJ);



    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $movimiento = MovimientoPieza::findOrFail($id);

            // Obtenés la sucursal origen desde el movimiento
            $sucursalOrigen = $movimiento->sucursal_origen_id;

            // Revertir todas las piezas que participaron en el movimiento
            foreach ($movimiento->piezaMovimientos as $um) {
                // Revertir la pieza a la sucursal original
                Pieza::where('id', $um->pieza_id)->update([
                    'sucursal_id' => $sucursalOrigen
                ]);

                // Eliminar el registro intermedio
                $um->delete();
            }

            // Finalmente, eliminar el movimiento
            $movimiento->delete();

            DB::commit();
            return redirect()->route('movimientoPiezas.index')->with('success', 'Movimiento eliminado y piezas revertidas a su sucursal original.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar movimiento: ' . $e->getMessage());
        }
    }

    public function generatePDF(Request $request,$attach = false)
    {
        $movimientoId = $request->query('movimientoPieza_id');
        $movimiento = MovimientoPieza::find($movimientoId);



        $template = 'movimientoPiezas.pdf';
        $piezaMovimientos = $movimiento->piezaMovimientos()->get();



        $data = [
            'remito' => $movimientoId,
            'fecha' => $movimiento->fecha,
            'origen' => $movimiento->sucursalOrigen,
            'destino' => $movimiento->sucursalDestino,
            'piezas' => $piezaMovimientos,
        ];
        //dd($data);




        $pdf = PDF::loadView($template, $data);

        $pdfPath = 'Movimiento_pieza_' . $movimientoId . '.pdf';

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
        $movimiento = MovimientoPieza::find($id);

        $users = \App\Models\User::orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $origens = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $destinos = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('movimientoPiezas.show', compact('movimiento','origens','destinos','users'));
    }

    private function obtenerMovimientosFiltrados($busqueda, $user_id, $sortColumn, $dir)
    {
        $movimientosQuery = MovimientoPieza::with(['piezaMovimientos.pieza'])
            ->leftJoin('sucursals as origen', 'movimiento_piezas.sucursal_origen_id', '=', 'origen.id')
            ->leftJoin('sucursals as destino', 'movimiento_piezas.sucursal_destino_id', '=', 'destino.id')
            ->leftJoin('users', 'movimiento_piezas.user_id', '=', 'users.id')
            ->select(
                'movimiento_piezas.id as id',
                DB::raw("IFNULL(users.name, movimiento_piezas.user_name) as usuario_nombre"),
                'origen.nombre as origen_nombre',
                'destino.nombre as destino_nombre',
                'movimiento_piezas.fecha'
            );

        // FILTRO POR USUARIO
        if (!empty($user_id) && $user_id != '-1') {
            $movimientosQuery->where('movimiento_piezas.user_id', $user_id);
        }

        $movimientos = $movimientosQuery->get();

        // MAPEO + CONCAT
        $datos = $movimientos->map(function ($movimiento) {
            return [
                'id' => $movimiento->id,
                'usuario_nombre' => $movimiento->usuario_nombre,
                'origen_nombre' => $movimiento->origen_nombre,
                'destino_nombre' => $movimiento->destino_nombre,
                'fecha' => $movimiento->fecha,
                'piezas' => $movimiento->piezaMovimientos->pluck('pieza.codigo')->filter()->implode(', ')
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

        $sheet->setTitle("Movimientos de piezas");

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
        $fileName = "movimientos_piezas.xlsx";
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
        $movimientosQuery = MovimientoPieza::with(['piezaMovimientos.pieza'])
            ->leftJoin('sucursals as origen', 'movimiento_piezas.sucursal_origen_id', '=', 'origen.id')
            ->leftJoin('sucursals as destino', 'movimiento_piezas.sucursal_destino_id', '=', 'destino.id')
            ->leftJoin('users', 'movimiento_piezas.user_id', '=', 'users.id')
            ->select(
                'movimiento_piezas.id as id',
                DB::raw("IFNULL(users.name, movimiento_piezas.user_name) as usuario_nombre"),
                'origen.nombre as origen_nombre',
                'destino.nombre as destino_nombre',
                'movimiento_piezas.fecha'
            );

        // FILTRAR POR USUARIO
        if (!empty($user_id) && $user_id != '-1') {
            $movimientosQuery->where('movimiento_piezas.user_id', $user_id);
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
                'pieas' => $movimiento->piezaMovimientos->pluck('pieza.codigo')->filter()->implode(', ')
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

        $pdf = PDF::loadView('movimientoPiezas.exportpdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('movimientoPiezas.exportpdf');
    }







}
