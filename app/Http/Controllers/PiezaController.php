<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\Ubicacion;
use App\Models\TipoPieza;
use App\Models\StockPieza;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Pieza;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PDF;
use Illuminate\Database\QueryException;
class PiezaController extends Controller
{
    use SanitizesInput;
    function __construct()
    {
        $this->middleware('permission:pieza-listar|pieza-crear|pieza-editar|pieza-eliminar|pieza-modificar-descripcion', ['only' => ['index','store']]);

        $this->middleware('permission:pieza-crear', ['only' => ['create','store']]);

        // Ahora ambos permisos pueden entrar a edit/update
        $this->middleware('permission:pieza-editar|pieza-modificar-descripcion', ['only' => ['edit','update']]);

        $this->middleware('permission:pieza-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $piezas = Pieza::all();
        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1');
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todos', '-1');
        return view ('piezas.index',compact('piezas','sucursals','tipos'));
    }

    public function dataTable(Request $request)
    {
        $columnas = [
            'codigo',
            'descripcion',
            'tipo_pieza',
            'stock_minimo',
            'stock_actual',
            'sucursal_nombre',
            'ubicacion_nombre',
            'observaciones'
        ];

        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');
        $sucursal_id = $request->input('sucursal_id');
        $ubicacion_id = $request->input('ubicacion_id');
        $tipo_id = $request->input('tipo_id');

        // ----------------------------------------------
        // 1) QUERY BASE PARA COUNT CORRECTO
        // ----------------------------------------------
        $queryBase = Pieza::query()
            ->leftJoin('pieza_ubicacions', 'piezas.id', '=', 'pieza_ubicacions.pieza_id')
            ->leftJoin('ubicacions', 'pieza_ubicacions.ubicacion_id', '=', 'ubicacions.id');

        if ($sucursal_id && $sucursal_id != '-1') {
            $queryBase->where('ubicacions.sucursal_id', $sucursal_id);
        }

        if ($ubicacion_id && $ubicacion_id != '-1') {
            $queryBase->where('ubicacions.id', $ubicacion_id);
        }

        if (!empty($tipo_id) && $tipo_id != '-1') {
            $queryBase->where('piezas.tipo_pieza_id', $tipo_id);
        }

        if (!empty($busqueda)) {
            $queryBase->where(function ($sub) use ($busqueda) {
                $sub->orWhere('piezas.codigo', 'like', "%$busqueda%")
                    ->orWhere('piezas.descripcion', 'like', "%$busqueda%");
            });
        }

        $recordsFiltered = $queryBase->distinct()->count('piezas.id');
        $recordsTotal = Pieza::count();


        // ----------------------------------------------
        // 2) SUBQUERY PARA DATOS AGRUPADOS (GROUP_CONCAT)
        // ----------------------------------------------
        $subquery = Pieza::select(
            'piezas.id',
            'piezas.codigo',
            'piezas.descripcion',
            'tipo_piezas.nombre as tipo_pieza',
            'piezas.stock_minimo',
            'piezas.stock_actual',
            DB::raw("GROUP_CONCAT(DISTINCT sucursals.nombre ORDER BY sucursals.nombre SEPARATOR ' / ') as sucursal_nombre"),
            DB::raw("GROUP_CONCAT(DISTINCT ubicacions.nombre ORDER BY ubicacions.nombre SEPARATOR ' / ') as ubicacion_nombre"),
            'piezas.observaciones'
        )
            ->leftJoin('tipo_piezas', 'piezas.tipo_pieza_id', '=', 'tipo_piezas.id')
            ->leftJoin('pieza_ubicacions', 'piezas.id', '=', 'pieza_ubicacions.pieza_id')
            ->leftJoin('ubicacions', 'pieza_ubicacions.ubicacion_id', '=', 'ubicacions.id')
            ->leftJoin('sucursals', 'ubicacions.sucursal_id', '=', 'sucursals.id')
            ->groupBy(
                'piezas.id',
                'piezas.codigo',
                'piezas.descripcion',
                'tipo_piezas.nombre',
                'piezas.stock_minimo',
                'piezas.stock_actual',
                'piezas.observaciones'
            );

        // aplicar filtros igual que en base
        if ($sucursal_id && $sucursal_id != '-1') {
            $subquery->where('ubicacions.sucursal_id', $sucursal_id);
        }

        if ($ubicacion_id && $ubicacion_id != '-1') {
            $subquery->where('ubicacions.id', $ubicacion_id);
        }

        if (!empty($tipo_id) && $tipo_id != '-1') {
            $subquery->where('piezas.tipo_pieza_id', $tipo_id);
        }

        if (!empty($busqueda)) {
            $subquery->where(function ($q) use ($busqueda) {
                $q->orWhere('piezas.codigo', 'like', "%$busqueda%")
                    ->orWhere('piezas.descripcion', 'like', "%$busqueda%");
            });
        }


        // ----------------------------------------------
        // 3) PAGINAR SOBRE EL SUBQUERY AGRUPADO
        // ----------------------------------------------
        $queryFinal = DB::table(DB::raw("({$subquery->toSql()}) as t"))
            ->mergeBindings($subquery->getQuery())
            ->orderBy($columnaOrden, $orden)
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();


        return response()->json([
            'data' => $queryFinal,
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
        $sucursales = Sucursal::where('activa', 1)
            ->orderBy('nombre')
            ->get();
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        return view('piezas.create',compact('tipos','sucursales'));
    }

    /**
     * Store a newly created resource in storage.s
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'codigo' => 'required',
            'descripcion' => 'required',
            'tipo_pieza_id' => 'required',
        ]);

        $input = $this->sanitizeInput($request->all());

        // Si se capturó foto desde la cámara
        if ($request->filled('foto')) {
            $data = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('foto'));
            $image = base64_decode($data);

            $fileName = 'pieza_' . time() . '.png';
            $filePath = 'images/piezas/'.$fileName;

            // Guardar directamente en public
            file_put_contents(public_path($filePath), $image);

            $input['foto'] = 'piezas/'.$fileName; // se guarda la ruta en DB
        }

        try {
            // 1. Crear la pieza
            $pieza = Pieza::create($input);

            // 2. Asociar ubicación si se seleccionó
            if ($request->filled('ubicacion_id')) {
                $pieza->ubicacions()->attach($request->ubicacion_id);
            }
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()
                    ->with('error', 'El código ingresado ya existe. Debe ser único.')
                    ->withInput();
            }

            throw $e;
        }
        return redirect()->route('piezas.index')
            ->with('success','Pieza creada con éxito');
    }


    public function ajaxStore(Request $request)
    {
        $this->validate($request, [
            'codigo' => 'required',
            'descripcion' => 'required',
            'tipo_pieza_id' => 'required',
        ]);

        $input = $this->sanitizeInput($request->all());
// Si se capturó foto desde la cámara
        if ($request->filled('foto')) {
            $data = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('foto'));
            $image = base64_decode($data);

            $fileName = 'pieza_' . time() . '.png';
            $filePath = 'images/piezas/'.$fileName;

            // Guardar directamente en public
            file_put_contents(public_path($filePath), $image);

            $input['foto'] = 'piezas/'.$fileName; // se guarda la ruta en DB
        }
        $pieza = Pieza::create($input);

        // Devolver JSON
        return response()->json([
            'id' => $pieza->id,
            'codigo' => $pieza->codigo,
            'descripcion' => $pieza->descripcion
        ]);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pieza = Pieza::find($id);
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        // Agrupar stocks por sucursal y sumar la cantidad
        $stocksPorSucursal = StockPieza::where('pieza_id', $id)
            ->selectRaw('sucursal_id, SUM(cantidad) as total_cantidad')
            ->groupBy('sucursal_id')
            ->with('sucursal') // para poder acceder al nombre de la sucursal
            ->get();
        return view('piezas.show',compact('pieza','tipos','stocksPorSucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pieza = Pieza::find($id);
        $sucursales = Sucursal::where('activa', 1)
            ->orderBy('nombre')
            ->get();
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        // 🔥 Construimos un array simple para JS
        $ubicacionesActuales = $pieza->ubicacions->map(function ($u) {
            return [
                'sucursal_id'  => $u->pivot->sucursal_id, // valor que debe aparecer en el select de sucursal
                'ubicacion_id' => $u->id,                 // valor que debe aparecer en el select de ubicacion
            ];
        });

        return view('piezas.edit', compact('pieza','tipos','sucursales','ubicacionesActuales'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $pieza = Pieza::findOrFail($id);

        // Si tiene permiso para editar todo
        if ($request->user()->can('pieza-editar')) {
            $this->validate($request, [
                'codigo' => 'required|string|max:100',
                'descripcion' => 'required|string|max:500',
                'tipo_pieza_id' => 'required|integer|exists:tipo_piezas,id',
            ]);

            $input = $this->sanitizeInput($request->all());
            // Manejar la foto si viene en base64
            if ($request->filled('foto')) {
                $data = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('foto'));
                $image = base64_decode($data);

                $fileName = 'pieza_' . time() . '.png';
                $filePath = 'images/piezas/'.$fileName;

                // Guardar directamente en public
                file_put_contents(public_path($filePath), $image);

                $input['foto'] = 'piezas/'.$fileName;
            }
            try{
                $pieza->update($input);

                // -----------------------------
                //    GUARDAR UBICACIONES
                // -----------------------------

                // ubicacion_id[] viene del formulario
                $ubicaciones = $request->input('ubicacion_id', []);

                // Eliminar vacíos ("" o null)
                $ubicaciones = array_filter($ubicaciones);

                // Solo guarda ubicacion_id
                $pieza->ubicacions()->sync($ubicaciones);
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    return back()
                        ->with('error', 'El código ingresado ya existe. No puede duplicarse.')
                        ->withInput();
                }

                throw $e;
            }
        }
        // Si solo puede modificar descripción
        elseif ($request->user()->can('pieza-modificar-descripcion')) {
            $this->validate($request, [
                'descripcion' => 'required|string|max:500',
            ]);

            $pieza->descripcion = $request->input('descripcion');
            $pieza->save();
        }
        // Si no tiene permisos, prohibir
        else {
            abort(403, 'No tienes permisos para editar esta pieza.');
        }

        return redirect()->route('piezas.index')
            ->with('success','Pieza modificada con éxito');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $pieza = Pieza::findOrFail($id);

            // Intentar borrar
            $pieza->delete();

            // Si tenía foto, borrar archivo
            if ($pieza->foto && file_exists(public_path('images/'.$pieza->foto))) {
                unlink(public_path('images/'.$pieza->foto));
            }

            return redirect()->route('piezas.index')
                ->with('success', 'Pieza eliminada con éxito');

        } catch (\Illuminate\Database\QueryException $e) {

            // Error 1451 = clave foránea impide borrar
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('piezas.index')
                    ->with('error', 'No se puede eliminar la pieza porque está asociada a ventas.');
            }

            return redirect()->route('piezas.index')
                ->with('error', 'Ocurrió un error al intentar eliminar la pieza.');
        }
    }




    public function getDatos($id)
    {
        $pieza = Pieza::find($id);

        if (!$pieza) {
            return response()->json(['error' => 'No encontrada'], 404);
        }

        return response()->json([
            'costo' => $pieza->costo,
            'precio_minimo' => $pieza->precio_minimo,
        ]);
    }

    public function createMasivo()
    {
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        return view('piezas.masivo', compact('tipos'));
    }


    public function storeMasivo(Request $request)
    {
        $codigos = $request->input('codigo', []);
        $tipos = $request->input('tipo_pieza_id', []);
        $descripciones = $request->input('descripcion', []);
        $fotos = $request->input('foto', []);

        $count = count($codigos);

        $dataToInsert = [];

        for ($i = 0; $i < $count; $i++) {
            // Validación simple por fila
            if (empty($codigos[$i]) || empty($tipos[$i]) || empty($descripciones[$i])) {
                return redirect()->back()->withErrors("La fila " . ($i+1) . " tiene campos vacíos")->withInput();
            }

            $data = [
                'codigo' => $codigos[$i],
                'tipo_pieza_id' => $tipos[$i],
                'descripcion' => $descripciones[$i],
                'foto' => null
            ];

            // Procesar foto Base64 si existe
            if (!empty($fotos[$i])) {
                $image = preg_replace('#^data:image/\w+;base64,#i', '', $fotos[$i]);
                $image = base64_decode($image);

                $fileName = 'pieza_' . time() . '_' . uniqid() . '.png';
                $filePath = 'images/piezas/' . $fileName;

                file_put_contents(public_path($filePath), $image);

                $data['foto'] = 'piezas/' . $fileName;
            }

            $dataToInsert[] = $data;
        }

        // Insertar todas las piezas de una vez
        Pieza::insert($dataToInsert);

        return redirect()->route('piezas.index')
            ->with('success', 'Piezas cargadas correctamente');
    }


    public function exportarXLS(Request $request)
    {
        $columnas = [
            'piezas.codigo',
            'piezas.descripcion',
            'tipo_piezas.nombre',
            'piezas.stock_minimo',
            'piezas.stock_actual',
            'sucursals.nombre',
            'ubicacions.nombre',
            'piezas.observaciones'
        ];

        $busqueda = $request->search;
        $sucursal_id = $request->sucursal_id;
        $ubicacion_id = $request->ubicacion_id;
        $tipo_id = $request->tipo_id;

        // ------------------------------
        // OBTENER NOMBRES DE LOS FILTROS
        // ------------------------------
        $sucursalNombre = ($sucursal_id && $sucursal_id != -1)
            ? (Sucursal::find($sucursal_id)->nombre ?? '—')
            : 'Todas';

        $ubicacionNombre = ($ubicacion_id && $ubicacion_id != -1)
            ? (Ubicacion::find($ubicacion_id)->nombre ?? '—')
            : 'Todas';

        $tipoNombre = ($tipo_id && $tipo_id != -1)
            ? (TipoPieza::find($tipo_id)->nombre ?? '—')
            : 'Todos';

        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = Pieza::select(
            'piezas.codigo',
            'piezas.descripcion',
            'tipo_piezas.nombre as tipo_pieza',
            'piezas.stock_minimo',
            'piezas.stock_actual',
            DB::raw("GROUP_CONCAT(DISTINCT sucursals.nombre ORDER BY sucursals.nombre SEPARATOR ' / ') as sucursal_nombre"),
            DB::raw("GROUP_CONCAT(DISTINCT ubicacions.nombre ORDER BY ubicacions.nombre SEPARATOR ' / ') as ubicacion_nombre"),
            'piezas.observaciones'
        )
            ->leftJoin('tipo_piezas', 'piezas.tipo_pieza_id', '=', 'tipo_piezas.id')
            ->leftJoin('pieza_ubicacions', 'piezas.id', '=', 'pieza_ubicacions.pieza_id')
            ->leftJoin('ubicacions', 'pieza_ubicacions.ubicacion_id', '=', 'ubicacions.id')
            ->leftJoin('sucursals', 'ubicacions.sucursal_id', '=', 'sucursals.id')
            ->groupBy(
                'piezas.id',
                'piezas.codigo',
                'piezas.descripcion',
                'tipo_piezas.nombre',
                'piezas.stock_minimo',
                'piezas.stock_actual',
                'piezas.observaciones'
            );

        if ($sucursal_id && $sucursal_id != '-1') {
            $query->where('ubicacions.sucursal_id', $sucursal_id);
        }

        if ($ubicacion_id && $ubicacion_id != '-1') {
            $query->where('ubicacions.id', $ubicacion_id);
        }

        if (!empty($tipo_id) && $tipo_id != '-1') {
            $query->where('piezas.tipo_pieza_id', $tipo_id);
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
        $sheet->setTitle("Piezas");

        // ------------------------------
        // FILTROS
        // ------------------------------
        $sheet->setCellValue('A1', 'Sucursal:');
        $sheet->setCellValue('B1', $sucursalNombre);

        $sheet->setCellValue('A2', 'Ubicación:');
        $sheet->setCellValue('B2', $ubicacionNombre);

        $sheet->setCellValue('A3', 'Tipo:');
        $sheet->setCellValue('B3', $tipoNombre);

        $sheet->setCellValue('A4', 'Búsqueda:');
        $sheet->setCellValue('B4', $busqueda ?: '—');

        // Espacio antes de la tabla
        $startRow = 5;

        // ------------------------------
        // ENCABEZADOS DE LA TABLA
        // ------------------------------
        $headers = [
            "Código", "Descripción", "Tipo", "Stock mínimo",
            "Stock actual", "Sucursal", "Ubicación", "Observaciones"
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
            $sheet->setCellValue("A{$row}", $p->codigo);
            $sheet->setCellValue("B{$row}", $p->descripcion);
            $sheet->setCellValue("C{$row}", $p->tipo_pieza);
            $sheet->setCellValue("D{$row}", $p->stock_minimo);
            $sheet->setCellValue("E{$row}", $p->stock_actual);
            $sheet->setCellValue("F{$row}", $p->sucursal_nombre);
            $sheet->setCellValue("G{$row}", $p->ubicacion_nombre);
            $sheet->setCellValue("H{$row}", $p->observaciones);
            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "piezas.xlsx";
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
            'piezas.codigo',
            'piezas.descripcion',
            'tipo_piezas.nombre',
            'piezas.stock_minimo',
            'piezas.stock_actual',
            'sucursals.nombre',
            'ubicacions.nombre',
            'piezas.observaciones'
        ];

        $busqueda = $request->search;
        $sucursal_id = $request->sucursal_id;
        $ubicacion_id = $request->ubicacion_id;
        $tipo_id = $request->tipo_id;
        $sucursalNombre = Sucursal::find($sucursal_id)->nombre ?? 'Todas';
        $ubicacionNombre = Ubicacion::find($ubicacion_id)->nombre ?? 'Todas';
        $tipoNombre = TipoPieza::find($tipo_id)->nombre ?? 'Todas';

        // MISMA QUERY QUE EN dataTable
        $query = Pieza::select(
            'piezas.codigo',
            'piezas.descripcion',
            'tipo_piezas.nombre as tipo_pieza',
            'piezas.stock_minimo',
            'piezas.stock_actual',
            DB::raw("GROUP_CONCAT(DISTINCT sucursals.nombre ORDER BY sucursals.nombre SEPARATOR ' / ') as sucursal_nombre"),
            DB::raw("GROUP_CONCAT(DISTINCT ubicacions.nombre ORDER BY ubicacions.nombre SEPARATOR ' / ') as ubicacion_nombre"),
            'piezas.observaciones'
        )
            ->leftJoin('tipo_piezas', 'piezas.tipo_pieza_id', '=', 'tipo_piezas.id')
            ->leftJoin('pieza_ubicacions', 'piezas.id', '=', 'pieza_ubicacions.pieza_id')
            ->leftJoin('ubicacions', 'pieza_ubicacions.ubicacion_id', '=', 'ubicacions.id')
            ->leftJoin('sucursals', 'ubicacions.sucursal_id', '=', 'sucursals.id')
            ->groupBy(
                'piezas.id',
                'piezas.codigo',
                'piezas.descripcion',
                'tipo_piezas.nombre',
                'piezas.stock_minimo',
                'piezas.stock_actual',
                'piezas.observaciones'
            );

        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('ubicacions.sucursal_id', $sucursal_id);
        }

        if (!empty($ubicacion_id) && $ubicacion_id != '-1') {
            $query->where('ubicacions.id', $ubicacion_id);
        }

        if (!empty($tipo_id) && $tipo_id != '-1') {
            $query->where('piezas.tipo_pieza_id', $tipo_id);
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
            'sucursalNombre' => $sucursalNombre,
            'ubicacionNombre' => $ubicacionNombre,
            'tipoNombre' => $tipoNombre,
        ];

        $pdf = PDF::loadView('piezas.pdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('piezas.pdf');
    }




}
