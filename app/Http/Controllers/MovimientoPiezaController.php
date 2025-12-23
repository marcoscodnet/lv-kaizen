<?php

namespace App\Http\Controllers;

use App\Models\MovimientoPieza;

use App\Models\Sucursal;
use App\Models\Pieza;
use App\Models\StockPieza;
use App\Models\PiezaMovimiento;
use App\Models\User;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PDF;
use DB;
class MovimientoPiezaController extends Controller
{
    use SanitizesInput;
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
            'piezas',
            'estado_orden', // 👈 esta manda el orden
            'estado',
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


        $piezas = Pieza::get()
            ->mapWithKeys(function ($pieza) {
                $texto = ($pieza->codigo ?? '') . ' - '
                    . ($pieza->descripcion ?? '') ;

                return [$pieza->id => $texto];
            })
            ->prepend('', ''); // si necesitas un vacío al principio
        $origens = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $destinos = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('movimientoPiezas.create', compact('piezas','origens','destinos'));
    }

    public function store(Request $request)
    {
        $rules = [
            'sucursal_origen_id' => 'required',
            'sucursal_destino_id' => 'required',
            'fecha' => 'required|date',
            'pieza_id' => 'required|array|min:1',
            'pieza_id.*' => 'required|distinct',
            'cantidad' => 'required|array',
            'cantidad.*' => 'required|integer|min:1',
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

        foreach ($request->pieza_id as $index => $piezaId) {

            $cantidadSolicitada = $request->cantidad[$index];

            $stockDisponible = StockPieza::where('pieza_id', $piezaId)
                ->where('sucursal_id', $request->sucursal_origen_id)
                ->sum('cantidad');

            if ($stockDisponible < $cantidadSolicitada) {
                return redirect()->back()
                    ->withErrors([
                        'stock' => 'Stock insuficiente para la pieza ID '.$piezaId.
                            '. Disponible: '.$stockDisponible.
                            ', solicitado: '.$cantidadSolicitada
                    ])
                    ->withInput();
            }
        }

        $input = $this->sanitizeInput($request->all());
        // Obtener el ID del usuario autenticado
        $userId = Auth::id();
        $input['user_id'] = $userId;

        DB::beginTransaction();
        $ok=1;
        try {
            /*$input['estado'] = 'PENDIENTE';*/
            $movimiento = MovimientoPieza::create($input);

            $lastid=$movimiento->id;
            if(count($request->pieza_id) > 0)
            {
                foreach ($request->pieza_id as $item => $piezaId) {

                    $cantidad = $request->cantidad[$item];
                    try {
                        // 1️⃣ Guardar detalle del movimiento
                        PiezaMovimiento::create([
                            'movimientoPieza_id' => $lastid,
                            'pieza_id' => $piezaId,
                            'cantidad' => $cantidad
                        ]);


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
            $movimiento = MovimientoPieza::with('piezaMovimientos')->findOrFail($id);

            // ============================
            // 1️⃣ SI ESTÁ PENDIENTE
            // ============================
            if ($movimiento->estado === 'Pendiente') {

                // Eliminar detalles
                $movimiento->piezaMovimientos()->delete();

                // Eliminar movimiento
                $movimiento->delete();

                DB::commit();

                return redirect()
                    ->route('movimientoPiezas.index')
                    ->with('success', 'Movimiento pendiente eliminado (no afectaba stock).');
            }

            // ============================
            // 2️⃣ SI NO ES ACEPTADO → ERROR
            // ============================
            if ($movimiento->estado !== 'aceptado') {
                throw new \Exception('Estado de movimiento inválido.');
            }

            // ============================
            // 3️⃣ REVERTIR STOCK (ACEPTADO)
            // ============================
            $sucursalOrigen  = $movimiento->sucursal_origen_id;
            $sucursalDestino = $movimiento->sucursal_destino_id;

            foreach ($movimiento->piezaMovimientos as $pm) {

                $piezaId  = $pm->pieza_id;
                $cantidad = $pm->cantidad;

                // 🔻 RESTAR DESTINO (FIFO)
                $stocksDestino = StockPieza::where('pieza_id', $piezaId)
                    ->where('sucursal_id', $sucursalDestino)
                    ->where('cantidad', '>', 0)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $restante = $cantidad;

                foreach ($stocksDestino as $stock) {
                    if ($restante <= 0) break;

                    if ($stock->cantidad <= $restante) {
                        $restante -= $stock->cantidad;
                        $stock->delete();
                    } else {
                        $stock->cantidad -= $restante;
                        $stock->save();
                        $restante = 0;
                    }
                }

                if ($restante > 0) {
                    throw new \Exception(
                        'Stock inconsistente al revertir la pieza ID ' . $piezaId
                    );
                }

                // 🔺 SUMAR ORIGEN
                StockPieza::create([
                    'pieza_id'    => $piezaId,
                    'sucursal_id' => $sucursalOrigen,
                    'cantidad'    => $cantidad,
                    'ingreso'     => $movimiento->fecha,
                    'inicial'     => 0
                ]);

                // borrar detalle
                $pm->delete();
            }

            // ============================
            // 4️⃣ ELIMINAR MOVIMIENTO
            // ============================
            $movimiento->delete();

            DB::commit();

            return redirect()
                ->route('movimientoPiezas.index')
                ->with('success', 'Movimiento aceptado eliminado y stock revertido.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Error al eliminar movimiento: ' . $e->getMessage());
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
            ->leftJoin('users as acepta', 'movimiento_piezas.user_acepta_id', '=', 'acepta.id')
            ->select(
                'movimiento_piezas.*',
                'users.name as usuario_nombre',
                'acepta.name as acepta_nombre',
                'origen.nombre as origen_nombre',
                'destino.nombre as destino_nombre'
            );

        // FILTRO POR USUARIO
        if (!empty($user_id) && $user_id != '-1') {
            $movimientosQuery->where('movimiento_piezas.user_id', $user_id);
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
                'piezas' => $movimiento->piezaMovimientos
                    ->map(function ($pm) {
                        // Verifica si la pieza está disponible
                        if (!$pm->pieza) {
                            return null;
                        }
                        // Devuelve el código de la pieza y la cantidad
                        return $pm->pieza->codigo . ' (' . $pm->cantidad . ')';
                    })
                    ->filter() // Elimina los valores nulos
                    ->implode(', ') // Concatena las piezas con una coma
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
                    || str_contains(mb_strtolower($item['estado']), $busqueda)
                    || str_contains(mb_strtolower($item['piezas']), $busqueda);
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
            "Usuario", "Origen", "Destino", "Fecha", "Piezas"
        ];

        $row = 4;
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
            $sheet->setCellValue("E$row", $m['piezas']);
            $row++;
        }

        // AUTO SIZE
        foreach (range('A', 'E') as $col) {
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
                'movimiento_piezas.*',
                'users.name as usuario_nombre',
                'origen.nombre as origen_nombre',
                'destino.nombre as destino_nombre'
            );

        // FILTRAR POR USUARIO
        if (!empty($user_id) && $user_id != '-1') {
            $movimientosQuery->where('movimiento_piezas.user_id', $user_id);
        }

        $movimientos = $movimientosQuery->get();

        // ===============================
// MAPEO EXACTO AL DATATABLE
// ===============================
        $datos = $movimientos->map(function ($movimiento) {
            return [
                'id' => $movimiento->id,
                'usuario_nombre' => $movimiento->usuario_nombre,
                'origen_nombre' => $movimiento->origen_nombre,
                'destino_nombre' => $movimiento->destino_nombre,
                'fecha' => $movimiento->fecha
                    ? \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y')
                    : '',
                'piezas' => $movimiento->piezaMovimientos
                    ->map(function ($pm) {
                        if (!$pm->pieza) {
                            return null;
                        }
                        return $pm->pieza->codigo . ' (' . $pm->cantidad . ')';
                    })
                    ->filter()
                    ->implode(', ')
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
                    || str_contains(mb_strtolower($item['piezas']), $busqueda);
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


    public function aceptar(MovimientoPieza $movimiento)
    {
        // 🔐 Permiso
        if (!auth()->user()->can('pieza-movimiento-aceptar')) {
            abort(403);
        }

        // 🧠 Estado
        if ($movimiento->estado !== 'Pendiente') {
            return back()->with('error', 'El movimiento ya fue procesado');
        }

        // 🏢 Sucursal destino (excepto admin)
        if (
            auth()->user()->sucursal_id !== $movimiento->sucursal_destino_id &&
            !auth()->user()->hasRole('Administrador')
        ) {
            abort(403, 'No pertenece a la sucursal destino');
        }

        DB::beginTransaction();

        try {

            foreach ($movimiento->piezaMovimientos as $detalle) {

                $piezaId  = $detalle->pieza_id;
                $cantidad = $detalle->cantidad;

                // ==========================
                // 1️⃣ DESCONTAR STOCK ORIGEN (FIFO)
                // ==========================
                $restante = $cantidad;

                $stocksOrigen = StockPieza::where('pieza_id', $piezaId)
                    ->where('sucursal_id', $movimiento->sucursal_origen_id)
                    ->where('cantidad', '>', 0)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($stocksOrigen as $stock) {
                    if ($restante <= 0) break;

                    if ($stock->cantidad <= $restante) {
                        $restante -= $stock->cantidad;
                        $stock->cantidad = 0;
                    } else {
                        $stock->cantidad -= $restante;
                        $restante = 0;
                    }

                    $stock->save();
                }

                if ($restante > 0) {
                    throw new \Exception('Stock insuficiente para la pieza ID '.$piezaId);
                }

                // ==========================
                // 2️⃣ AGREGAR STOCK DESTINO
                // ==========================
                StockPieza::create([
                    'pieza_id'    => $piezaId,
                    'sucursal_id' => $movimiento->sucursal_destino_id,
                    'cantidad'    => $cantidad,
                    'ingreso'     => now(),
                    'inicial'     => 0,
                ]);
            }

            // ==========================
            // 3️⃣ ACTUALIZAR ESTADO
            // ==========================
            $movimiento->update([
                'estado'            => 'Aceptado',
                'fecha_aceptado'    => now(),
                'usuario_acepta_id' => auth()->id(),
            ]);

            DB::commit();

            return back()->with('success', 'Movimiento aceptado correctamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }






}
