<?php

namespace App\Http\Controllers;

use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Models\TipoUnidad;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Color;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;
class ProductoController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:producto-listar|producto-crear|producto-editar|producto-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:producto-crear', ['only' => ['create','store']]);
        $this->middleware('permission:producto-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:producto-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $productos = Producto::all();
        return view ('productos.index',compact('productos'));
    }


    public function dataTable(Request $request)
    {
       $columnas = ['tipo_unidads.nombre','marcas.nombre','modelos.nombre','colors.nombre','productos.precio','productos.minimo',
           DB::raw('COUNT(CASE WHEN v.id IS NULL THEN 1 END)'),DB::raw("CASE WHEN productos.discontinuo = 1 THEN 'SI' ELSE 'NO' END")]; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');
        $discontinuo = $request->input('discontinuo');
        $filtroStockMinimo = $request->input('filtroStockMinimo');

        $columnasBusqueda = [
            'tipo_unidads.nombre',
            'marcas.nombre',
            'modelos.nombre',
            'colors.nombre',
            'productos.precio',
            'productos.minimo',
            DB::raw("CASE WHEN productos.discontinuo = 1 THEN 'SI' ELSE 'NO' END")
        ];

        $query = Producto::select(
            'productos.id as id',
            'tipo_unidads.nombre as tipo_unidad_nombre',
            'marcas.nombre as marca_nombre',
            'modelos.nombre as modelo_nombre',
            'colors.nombre as color_nombre',
            'productos.precio',
            'productos.minimo',
            DB::raw("CASE WHEN productos.discontinuo = 1 THEN 'SI' ELSE 'NO' END AS discontinuo"),
            DB::raw("COUNT(CASE WHEN v.id IS NULL THEN 1 END) as stock_actual")
        )
            ->leftJoin('tipo_unidads', 'productos.tipo_unidad_id', '=', 'tipo_unidads.id')
            ->leftJoin('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('colors', 'productos.color_id', '=', 'colors.id')
            ->leftJoin('unidads as u', 'u.producto_id', '=', 'productos.id')
            ->leftJoin('ventas as v', 'u.id', '=', 'v.unidad_id')
            ->groupBy(
                'productos.id',
                'tipo_unidads.nombre',
                'marcas.nombre',
                'modelos.nombre',
                'colors.nombre',
                'productos.precio',
                'productos.minimo',
                'productos.discontinuo'
            );


        if (!empty($discontinuo) && $discontinuo != '-1') {
            $query->where('productos.discontinuo', $discontinuo);
        }

        if (!empty($filtroStockMinimo) && $filtroStockMinimo != '-1') {
            $query->havingRaw('COUNT(CASE WHEN v.id IS NULL THEN 1 END) < productos.minimo');
        }

        // Aplicar la búsqueda
        if (!empty($busqueda)) {
            $query->where(function ($query) use ($columnasBusqueda, $busqueda) {
                foreach ($columnasBusqueda as $columna) {
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
        $recordsTotal = Producto::count();



        return response()->json([
            'data' => $datos, // Obtener solo los elementos paginados
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

        $tipoUnidads = TipoUnidad::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $modelos = Modelo::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $marcas = Marca::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $colors = Color::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('productos.create', compact('tipoUnidads','modelos','marcas','colors'));
    }

    public function store(Request $request)
    {
        $rules = [
            'tipo_unidad_id' => 'required',
            'marca_id' => 'required',
            'modelo_id' => 'required',
            'color_id' => 'required',
            'precio' => 'nullable|numeric', // puede ser vacío, o un número (decimal)
            'minimo' => 'nullable|integer', // puede ser vacío, o un entero

        ];

        // Definir los mensajes de error personalizados
        $messages = [

            'tipo_unidad_id.required' => 'El campo Tipo de Unidad es obligatorio.',
            'marca_id.required' => 'El campo Marca es obligatorio.',
            'modelo_id.required' => 'El campo Modelo es obligatorio.',
            'color_id.required' => 'El campo Color es obligatorio.',
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


        $producto = Producto::create($input);


        return redirect()->route('productos.index')
            ->with('success','Producto creado con éxito');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $producto = Producto::find($id);

        $tipoUnidads = TipoUnidad::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $modelos = Modelo::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $marcas = Marca::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $colors = Color::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('productos.edit', compact('producto','tipoUnidads','modelos','marcas','colors'));

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
            'tipo_unidad_id' => 'required',
            'marca_id' => 'required',
            'modelo_id' => 'required',
            'color_id' => 'required',
            'precio' => 'nullable|numeric', // puede ser vacío, o un número (decimal)
            'minimo' => 'nullable|integer', // puede ser vacío, o un entero

        ];

        // Definir los mensajes de error personalizados
        $messages = [

            'tipo_unidad_id.required' => 'El campo Tipo de Unidad es obligatorio.',
            'marca_id.required' => 'El campo Marca es obligatorio.',
            'modelo_id.required' => 'El campo Modelo es obligatorio.',
            'color_id.required' => 'El campo Color es obligatorio.',
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




        $producto = Producto::find($id);
        try {
            $producto->update($input);

        } catch (QueryException $ex) {

            if ($ex->errorInfo[1] == 1062) {
                $mensajeError = 'El Producto ya existe.';
            } else {
                $mensajeError = $ex->getMessage();
            }

            // Retornar al formulario con error
            return redirect()->back()
                ->withErrors(['error' => $mensajeError])
                ->withInput();

        } catch (\Exception $ex) {

            return redirect()->back()
                ->withErrors(['error' => $ex->getMessage()])
                ->withInput();
        }

        return redirect()->route('productos.index')
            ->with('success','Producto modificado con éxito');
    }


    public function updatePrecio(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:productos,id',
            'precio' => 'nullable|numeric'
        ]);

        $producto = Producto::findOrFail($request->id);
        $producto->precio = $request->precio;
        $producto->save();

        return response()->json(['success' => true]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $producto = Producto::find($id);

        $tipoUnidads = TipoUnidad::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $modelos = Modelo::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $marcas = Marca::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $colors = Color::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('productos.show', compact('producto','tipoUnidads','modelos','marcas','colors'));

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Producto::find($id)->delete();

        return redirect()->route('productos.index')
            ->with('success','Producto eliminado con éxito');
    }

    public function exportarXLS(Request $request)
    {
        $columnas = [
            'tipo_unidads.nombre',
            'marcas.nombre',
            'modelos.nombre',
            'colors.nombre',
            'productos.precio',
            'productos.minimo',
            DB::raw("CASE WHEN productos.discontinuo = 1 THEN 'SI' ELSE 'NO' END")
        ];

        $busqueda = $request->search;
        $discontinuo = $request->discontinuo;
        $filtroStockMinimo = $request->stockMinimo;

        // ------------------------------
        // OBTENER NOMBRES DE LOS FILTROS
        // ------------------------------
        $discontinuoNombre = (!empty($discontinuo) && $discontinuo != '-1')
            ? ($discontinuo?'SI':'NO')
            : 'Todos';

        $minimoNombre = (!empty($filtroStockMinimo) && $filtroStockMinimo != '-1')
            ? ($filtroStockMinimo?'SI':'NO')
            : 'Todos';

        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = Producto::select(
            'productos.id as id',
            'tipo_unidads.nombre as tipo_unidad_nombre',
            'marcas.nombre as marca_nombre',
            'modelos.nombre as modelo_nombre',
            'colors.nombre as color_nombre',
            'productos.precio',
            'productos.minimo',
            DB::raw("CASE WHEN productos.discontinuo = 1 THEN 'SI' ELSE 'NO' END AS discontinuo"),
            DB::raw("COUNT(CASE WHEN v.id IS NULL THEN 1 END) as stock_actual")
        )
            ->leftJoin('tipo_unidads', 'productos.tipo_unidad_id', '=', 'tipo_unidads.id')
            ->leftJoin('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('colors', 'productos.color_id', '=', 'colors.id')
            ->leftJoin('unidads as u', 'u.producto_id', '=', 'productos.id')
            ->leftJoin('ventas as v', 'u.id', '=', 'v.unidad_id')
            ->groupBy(
                'productos.id',
                'tipo_unidads.nombre',
                'marcas.nombre',
                'modelos.nombre',
                'colors.nombre',
                'productos.precio',
                'productos.minimo',
                'productos.discontinuo'
            );

        if (!empty($discontinuo) && $discontinuo != '-1') {
            $query->where('productos.discontinuo', $discontinuo);
        }

        if (!empty($filtroStockMinimo) && $filtroStockMinimo != '-1') {
            $query->havingRaw('COUNT(CASE WHEN v.id IS NULL THEN 1 END) < productos.minimo');
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $productos = $query->get();

        // ===============================
        //     📄 CREAR ARCHIVO XLSX
        // ===============================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Productos");

        // ------------------------------
        // FILTROS
        // ------------------------------
        $sheet->setCellValue('A1', 'Discontinuos:');
        $sheet->setCellValue('B1', $discontinuoNombre);

        $sheet->setCellValue('A2', 'Debajo del mínimo:');
        $sheet->setCellValue('B2', $minimoNombre);

        $sheet->setCellValue('A3', 'Búsqueda:');
        $sheet->setCellValue('B3', $busqueda ?: '—');

        // Espacio antes de la tabla
        $startRow = 5;

        // ------------------------------
        // ENCABEZADOS DE LA TABLA
        // ------------------------------
        $headers = [
            "Tipo", "Marca", "Modelo", "Color",
            "$ sugerido", "Stock mín.", "Stock Actual", "Discontinuo"
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

        foreach ($productos as $p) {
            $sheet->setCellValue("A{$row}", $p->tipo_unidad_nombre);
            $sheet->setCellValue("B{$row}", $p->marca_nombre);
            $sheet->setCellValue("C{$row}", $p->modelo_nombre);
            $sheet->setCellValue("D{$row}", $p->color_nombre);
            $sheet->setCellValue("E{$row}", $p->precio);
            $sheet->setCellValue("F{$row}", $p->minimo);
            $sheet->setCellValue("G{$row}", $p->stock_actual);
            $sheet->setCellValue("H{$row}", $p->discontinuo);
            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "productos.xlsx";
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
            'tipo_unidads.nombre',
            'marcas.nombre',
            'modelos.nombre',
            'colors.nombre',
            'productos.precio',
            'productos.minimo',
            DB::raw("CASE WHEN productos.discontinuo = 1 THEN 'SI' ELSE 'NO' END")
        ];

        $busqueda = $request->search;
        $discontinuo = $request->discontinuo;
        $filtroStockMinimo = $request->stockMinimo;

        // ------------------------------
        // OBTENER NOMBRES DE LOS FILTROS
        // ------------------------------
        $discontinuoNombre = (!empty($discontinuo) && $discontinuo != '-1')
            ? ($discontinuo?'SI':'NO')
            : 'Todos';

        $minimoNombre = (!empty($filtroStockMinimo) && $filtroStockMinimo != '-1')
            ? ($filtroStockMinimo?'SI':'NO')
            : 'Todos';

        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = Producto::select(
            'productos.id as id',
            'tipo_unidads.nombre as tipo_unidad_nombre',
            'marcas.nombre as marca_nombre',
            'modelos.nombre as modelo_nombre',
            'colors.nombre as color_nombre',
            'productos.precio',
            'productos.minimo',
            DB::raw("CASE WHEN productos.discontinuo = 1 THEN 'SI' ELSE 'NO' END AS discontinuo"),
            DB::raw("COUNT(CASE WHEN v.id IS NULL THEN 1 END) as stock_actual")
        )
            ->leftJoin('tipo_unidads', 'productos.tipo_unidad_id', '=', 'tipo_unidads.id')
            ->leftJoin('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('colors', 'productos.color_id', '=', 'colors.id')
            ->leftJoin('unidads as u', 'u.producto_id', '=', 'productos.id')
            ->leftJoin('ventas as v', 'u.id', '=', 'v.unidad_id')
            ->groupBy(
                'productos.id',
                'tipo_unidads.nombre',
                'marcas.nombre',
                'modelos.nombre',
                'colors.nombre',
                'productos.precio',
                'productos.minimo',
                'productos.discontinuo'
            );

        if (!empty($discontinuo) && $discontinuo != '-1') {
            $query->where('productos.discontinuo', $discontinuo);
        }

        if (!empty($filtroStockMinimo) && $filtroStockMinimo != '-1') {
            $query->havingRaw('COUNT(CASE WHEN v.id IS NULL THEN 1 END) < productos.minimo');
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $productos = $query->get();

        // Pasamos datos a la vista PDF
        $data = [
            'productos' => $productos,
            'busqueda' => $busqueda,
            'discontinuoNombre' => $discontinuoNombre,
            'minimoNombre' => $minimoNombre,
        ];

        $pdf = PDF::loadView('productos.pdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('productos.pdf');
    }

}
