<?php

namespace App\Http\Controllers;

use App\Models\Pieza;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\StockPieza;
use App\Models\TipoPieza;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;
class StockPiezaController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:stock-pieza-listar|stock-pieza-crear|stock-pieza-editar|stock-pieza-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:stock-pieza-crear', ['only' => ['create','store']]);
        $this->middleware('permission:stock-pieza-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:stock-pieza-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $stockPiezas = StockPieza::all();
        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1');
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todos', '-1');
        return view ('stockPiezas.index',compact('stockPiezas','sucursals','tipos'));
    }


    public function dataTable(Request $request)
    {
        $columnas = ['stock_piezas.remito','piezas.codigo','tipo_piezas.nombre','piezas.descripcion','stock_piezas.inicial','stock_piezas.cantidad','stock_piezas.costo','stock_piezas.precio_minimo','sucursals.nombre','proveedors.nombre','stock_piezas.ingreso','stock_piezas.id']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');
        $sucursal_id = $request->input('sucursal_id');
        $tipo_id = $request->input('tipo_id');
        $query = StockPieza::select('stock_piezas.id as id','stock_piezas.remito','piezas.codigo','tipo_piezas.nombre as tipo_nombre','piezas.descripcion','stock_piezas.inicial','stock_piezas.cantidad','stock_piezas.costo','stock_piezas.precio_minimo','sucursals.nombre as sucursal_nombre','proveedors.nombre as proveedor_nombre','stock_piezas.ingreso')
            ->leftJoin('piezas', 'stock_piezas.pieza_id', '=', 'piezas.id')
            ->leftJoin('tipo_piezas', 'piezas.tipo_pieza_id', '=', 'tipo_piezas.id')
            ->leftJoin('sucursals', 'stock_piezas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('proveedors', 'stock_piezas.proveedor_id', '=', 'proveedors.id')
            ;



        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('stock_piezas.sucursal_id', $sucursal_id);
        }

        if (!empty($tipo_id) && $tipo_id != '-1') {
            $query->where('piezas.tipo_pieza_id', $tipo_id);
        }

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




        // Clonar para evitar pisar el query
        $baseQuery = clone $query;

        // Totales
        //$totalPiezas = (clone $baseQuery)->count();

        // Suma del monto directamente desde la tabla servicios
        $totalPiezas = (clone $baseQuery)->sum('stock_piezas.cantidad');

        // Cantidad filtrada
        $recordsFiltered = (clone $baseQuery)->count();

        // Datos paginados
        $datos = (clone $baseQuery)
            ->orderBy($columnaOrden, $orden)
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();
        // Obtener la cantidad total de registros sin filtrar
        $recordsTotal = StockPieza::count();



        return response()->json([
            'data' => $datos, // Obtener solo los elementos paginados
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
            'totales' => [
                'totalPiezas' => $totalPiezas
            ]
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
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        $proveedors = Proveedor::orderBy('nombre')->pluck('nombre', 'id');
        return view('stockPiezas.create', compact('piezas','sucursals','tipos','proveedors'));
    }

    public function store(Request $request)
    {
        $rules = [
            'pieza_id' => 'required',
            'sucursal_id' => 'required',
            'proveedor' => 'required',

            'precio' => 'nullable|numeric', // puede ser vacío, o un número (decimal)
            'minimo' => 'nullable|integer', // puede ser vacío, o un entero

        ];

        // Definir los mensajes de error personalizados
        $messages = [

            'pieza_id.required' => 'El campo Pieza es obligatorio.',
            'sucursal_id.required' => 'El campo Sucursal es obligatorio.',

        ];

        // Crear el validador con las reglas y mensajes
        $validator = Validator::make($request->all(), $rules, $messages);

        // Validar y verificar si hay errores
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }




        DB::beginTransaction();

        $ok=1;




        try {
            $input = $this->sanitizeInput($request->all());
            $input['inicial'] = $input['cantidad'];
            $stockPieza = StockPieza::create($input);

            // 2. Obtener la suma total de cantidad para esa pieza
            $stockTotal = StockPieza::where('pieza_id', $request->pieza_id)
                ->sum('cantidad');

            // 3. Buscar la pieza y actualizar su stock_actual, costo y precio_minimo
            $pieza = Pieza::findOrFail($request->pieza_id);
            $pieza->stock_actual = $stockTotal;
            $pieza->costo = $this->sanitizeInput($request->costo);
            $pieza->precio_minimo = $this->sanitizeInput($request->precio_minimo);
            $pieza->save();


        }
        catch(QueryException $ex){
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


        return redirect()->route('stockPiezas.index')
            ->with($respuestaID,$respuestaMSJ);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $stockPieza = StockPieza::find($id);

        $piezas = Pieza::get()
            ->mapWithKeys(function ($pieza) {
                $texto = ($pieza->codigo ?? '') . ' - '
                    . ($pieza->descripcion ?? '') ;

                return [$pieza->id => $texto];
            })
            ->prepend('', ''); // si necesitas un vacío al principio
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $proveedors = Proveedor::orderBy('nombre')->pluck('nombre', 'id');
        return view('stockPiezas.edit', compact('stockPieza','piezas','sucursals','proveedors'));

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
        $rules = [

            'sucursal_id' => 'required',
            'proveedor' => 'required',

            'precio' => 'nullable|numeric', // puede ser vacío, o un número (decimal)
            'minimo' => 'nullable|integer', // puede ser vacío, o un entero

        ];

        // Definir los mensajes de error personalizados
        $messages = [

            'pieza_id.required' => 'El campo Pieza es obligatorio.',
            'sucursal_id.required' => 'El campo Sucursal es obligatorio.',

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




        $stockPieza = StockPieza::find($id);


        DB::beginTransaction();

        $ok=1;




        try {


            $stockPieza->update($input);

            // Recalcular el stock_actual de la pieza
            $stockTotal = StockPieza::where('pieza_id', $stockPieza->pieza_id)
                ->sum('cantidad');

            $pieza = Pieza::findOrFail($stockPieza->pieza_id);
            $pieza->stock_actual = $stockTotal;
            $pieza->costo = $this->sanitizeInput($request->costo);
            $pieza->precio_minimo = $this->sanitizeInput($request->precio_minimo);
            $pieza->save();

        }
        catch(QueryException $ex){
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


        return redirect()->route('stockPiezas.index')
            ->with($respuestaID,$respuestaMSJ);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stockPieza = StockPieza::find($id);

        $piezas = Pieza::get()
            ->mapWithKeys(function ($pieza) {
                $texto = ($pieza->codigo ?? '') . ' - '
                    . ($pieza->descripcion ?? '') ;

                return [$pieza->id => $texto];
            })
            ->prepend('', ''); // si necesitas un vacío al principio
        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $proveedors = Proveedor::orderBy('nombre')->pluck('nombre', 'id');
        return view('stockPiezas.show', compact('stockPieza','piezas','sucursals','proveedors'));

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        $ok = 1;

        try {
            $stockPieza = StockPieza::findOrFail($id);
            $pieza_id = $stockPieza->pieza_id;

            // eliminar registro
            $stockPieza->delete();

            // recalcular stock total de la pieza
            $stockTotal = StockPieza::where('pieza_id', $pieza_id)->sum('cantidad');

            // actualizar pieza
            $pieza = Pieza::find($pieza_id);
            if ($pieza) {
                $pieza->stock_actual = $stockTotal;
                $pieza->save();
            }

        } catch (\Exception $e) {
            $ok = 0;
            $error = $e->getMessage();
        }

        if ($ok) {
            DB::commit();
            return redirect()->route('stockPiezas.index')
                ->with('success', 'Stock Pieza eliminado con éxito');
        } else {
            DB::rollback();
            return redirect()->route('stockPiezas.index')
                ->with('error', $error);
        }
    }


    public function exportarXLS(Request $request)
    {
        $columnas = ['stock_piezas.remito','piezas.codigo','tipo_piezas.nombre','piezas.descripcion','stock_piezas.inicial','stock_piezas.cantidad','stock_piezas.costo','stock_piezas.precio_minimo','sucursals.nombre','proveedors.nombre','stock_piezas.ingreso','stock_piezas.id']; // Define las columnas disponibles

        $busqueda = $request->search;
        $sucursal_id = $request->sucursal_id;

        $tipo_id = $request->tipo_id;

        // ------------------------------
        // OBTENER NOMBRES DE LOS FILTROS
        // ------------------------------
        $sucursalNombre = ($sucursal_id && $sucursal_id != -1)
            ? (Sucursal::find($sucursal_id)->nombre ?? '—')
            : 'Todas';

        $tipoNombre = ($tipo_id && $tipo_id != -1)
            ? (TipoPieza::find($tipo_id)->nombre ?? '—')
            : 'Todos';

        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = StockPieza::select('stock_piezas.id as id','stock_piezas.remito','piezas.codigo','tipo_piezas.nombre as tipo_nombre','piezas.descripcion','stock_piezas.inicial','stock_piezas.cantidad','stock_piezas.costo','stock_piezas.precio_minimo','sucursals.nombre as sucursal_nombre','proveedors.nombre as proveedor_nombre','stock_piezas.ingreso')
            ->leftJoin('piezas', 'stock_piezas.pieza_id', '=', 'piezas.id')
            ->leftJoin('tipo_piezas', 'piezas.tipo_pieza_id', '=', 'tipo_piezas.id')
            ->leftJoin('sucursals', 'stock_piezas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('proveedors', 'stock_piezas.proveedor_id', '=', 'proveedors.id')
        ;

        if ($sucursal_id && $sucursal_id != '-1') {
            $query->where('ubicacions.sucursal_id', $sucursal_id);
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
        $sheet->setTitle("Stock Piezas");

        // ------------------------------
        // FILTROS
        // ------------------------------
        $sheet->setCellValue('A1', 'Sucursal:');
        $sheet->setCellValue('B1', $sucursalNombre);

        $sheet->setCellValue('A2', 'Tipo:');
        $sheet->setCellValue('B2', $tipoNombre);

        $sheet->setCellValue('A3', 'Búsqueda:');
        $sheet->setCellValue('B3', $busqueda ?: '—');

        // Espacio antes de la tabla
        $startRow = 5;

        // ------------------------------
        // ENCABEZADOS DE LA TABLA
        // ------------------------------
        $headers = [
            "Remito", "Código", "Tipo", "Descripción",
            "Cant. inicial", "Cant. Actual", "Costo", "Precio mín."
            , "Sucursal", "Proveedor", "Ingreso"
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
            $sheet->setCellValue("A{$row}", $p->remito);
            $sheet->setCellValue("B{$row}", $p->codigo);
            $sheet->setCellValue("C{$row}", $p->tipo_nombre);
            $sheet->setCellValue("D{$row}", $p->descripcion);
            $sheet->setCellValue("E{$row}", $p->inicial);
            $sheet->setCellValue("F{$row}", $p->cantidad);
            $sheet->setCellValue("G{$row}", $p->costo);
            $sheet->setCellValue("H{$row}", $p->precio_minimo);
            $sheet->setCellValue("I{$row}", $p->sucursal_nombre);
            $sheet->setCellValue("J{$row}", $p->proveedor_nombre);
            // 🟢 Formato de fecha dd/mm/YYYY
            $sheet->setCellValue("K{$row}",
                $p->ingreso
                    ? \Carbon\Carbon::parse($p->ingreso)->format('d/m/Y')
                    : '—'
            );
            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "stock_piezas.xlsx";
        $filePath = tempnam(sys_get_temp_dir(), $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }






    public function exportarPDF(Request $request)
    {
        ini_set('memory_limit', '-1'); // ilimitado
        ini_set('max_execution_time', 0);

        $columnas = ['stock_piezas.remito','piezas.codigo','tipo_piezas.nombre','piezas.descripcion','stock_piezas.inicial','stock_piezas.cantidad','stock_piezas.costo','stock_piezas.precio_minimo','sucursals.nombre','proveedors.nombre','stock_piezas.ingreso','stock_piezas.id']; // Define las columnas disponibles

        $busqueda = $request->search;
        $sucursal_id = $request->sucursal_id;

        $tipo_id = $request->tipo_id;
        $sucursalNombre = Sucursal::find($sucursal_id)->nombre ?? 'Todas';

        $tipoNombre = TipoPieza::find($tipo_id)->nombre ?? 'Todas';

        // MISMA QUERY QUE EN dataTable
        $query = StockPieza::select('stock_piezas.id as id','stock_piezas.remito','piezas.codigo','tipo_piezas.nombre as tipo_nombre','piezas.descripcion','stock_piezas.inicial','stock_piezas.cantidad','stock_piezas.costo','stock_piezas.precio_minimo','sucursals.nombre as sucursal_nombre','proveedors.nombre as procveedor_nombre','stock_piezas.ingreso')
            ->leftJoin('piezas', 'stock_piezas.pieza_id', '=', 'piezas.id')
            ->leftJoin('tipo_piezas', 'piezas.tipo_pieza_id', '=', 'tipo_piezas.id')
            ->leftJoin('sucursals', 'stock_piezas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('proveedors', 'stock_piezas.proveedor_id', '=', 'proveedors.id')
        ;

        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('ubicacions.sucursal_id', $sucursal_id);
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
            'tipoNombre' => $tipoNombre,
        ];

        $pdf = PDF::loadView('stockPiezas.pdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('stockPiezas.pdf');
    }

    public function createMasivo()
    {
        $piezas = Pieza::get()
            ->mapWithKeys(function ($pieza) {
                $texto = ($pieza->codigo ?? '') . ' - '
                    . ($pieza->descripcion ?? '') ;

                return [$pieza->id => $texto];
            })
            ->prepend('', ''); // si necesitas un vacío al principio
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        $proveedors = Proveedor::orderBy('nombre')->pluck('nombre', 'id');
        return view('stockPiezas.masivo', compact('piezas','sucursals','tipos','proveedors'));
    }

    public function storeMasivo(Request $request)
    {
        $rules = [
            'rows'                            => 'required|array|min:1',
            'rows.*.pieza_id'                 => 'required|integer|exists:piezas,id',
            'rows.*.sucursal_id'              => 'required|integer|exists:sucursals,id',
            'rows.*.proveedor_id'             => 'nullable|integer|exists:proveedors,id',
            'rows.*.cantidad'                 => 'required|numeric|min:1',

        ];

        $messages = [
            'rows.*.pieza_id.required'   => 'El campo Pieza es obligatorio.',
            'rows.*.sucursal_id.required'=> 'El campo Sucursal es obligatorio.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        $ok = 1;

        try {

            foreach ($request->rows as $row) {

                // Limpieza
                $input = $this->sanitizeInput($row);

                // Inicial es igual a la cantidad ingresada
                $input['inicial'] = $input['cantidad'];

                // Crear stock pieza
                $stockPieza = StockPieza::create($input);

                // Recalcular stock total por pieza
                $stockTotal = StockPieza::where('pieza_id', $row['pieza_id'])
                    ->sum('cantidad');

                // Actualizar pieza
                $pieza = Pieza::findOrFail($row['pieza_id']);
                $pieza->stock_actual  = $stockTotal;

                $pieza->save();
            }

        } catch (\Exception $ex) {
            $ok = 0;
            $error = $ex->getMessage();
        }

        if ($ok) {
            DB::commit();
            return redirect()->route('stockPiezas.index')
                ->with('success', 'Stock cargado satisfactoriamente');
        } else {
            DB::rollback();
            return redirect()->route('stockPiezas.index')
                ->with('error', $error);
        }
    }



}
