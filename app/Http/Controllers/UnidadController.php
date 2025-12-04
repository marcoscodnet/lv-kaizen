<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Producto;
use App\Models\Unidad;
use App\Models\TipoUnidad;
use App\Models\Sucursal;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;
class UnidadController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:unidad-listar|unidad-crear|unidad-editar|unidad-eliminar|unidad-modificar-envio', ['only' => ['index','store']]);

        $this->middleware('permission:unidad-crear', ['only' => ['create','store']]);

        // Ahora permitimos tanto editar completo como modificar solo envío
        $this->middleware('permission:unidad-editar|unidad-modificar-envio', ['only' => ['edit','update']]);

        $this->middleware('permission:unidad-eliminar', ['only' => ['destroy']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $unidads = Unidad::all();
        return view ('unidads.index',compact('unidads'));
    }


    public function dataTable(Request $request)
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
            ->leftJoin('colors', 'productos.color_id', '=', 'colors.id');

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
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('unidads.create', compact('productos','sucursals'));
    }

    public function store(Request $request)
    {
        $rules = [
            'producto_id' => 'required',
            'sucursal_id' => 'required',
            'motor' => 'required',
            'cuadro' => 'required',
            'precio' => 'nullable|numeric', // puede ser vacío, o un número (decimal)
            'minimo' => 'nullable|integer', // puede ser vacío, o un entero

        ];

        // Definir los mensajes de error personalizados
        $messages = [

            'producto_id.required' => 'El campo Producto es obligatorio.',
            'sucursal_id.required' => 'El campo Sucursal es obligatorio.',
            'motor.required' => 'El campo Nro. Motor es obligatorio.',
            'cuadro.required' => 'El campo Nro. Cuadro es obligatorio.',
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


        $unidad = Unidad::create($input);


        return redirect()->route('unidads.index')
            ->with('success','Unidad creada con éxito');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $unidad = Unidad::find($id);

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
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('unidads.edit', compact('unidad','productos','sucursals'));

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
        $unidad = Unidad::findOrFail($id);

        // Si tiene permiso para editar todo
        if ($request->user()->can('unidad-editar')) {
            $rules = [
                'producto_id' => 'required',
                'sucursal_id' => 'required',
                'motor' => 'required',
                'cuadro' => 'required',
                'precio' => 'nullable|numeric',
                'minimo' => 'nullable|integer',
            ];

            $messages = [
                'producto_id.required' => 'El campo Producto es obligatorio.',
                'sucursal_id.required' => 'El campo Sucursal es obligatorio.',
                'motor.required' => 'El campo Nro. Motor es obligatorio.',
                'cuadro.required' => 'El campo Nro. Cuadro es obligatorio.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $input = $this->sanitizeInput($request->all());

            try {
                $unidad->update($input);
            } catch (QueryException $ex) {
                $mensajeError = ($ex->errorInfo[1] == 1062)
                    ? 'El Producto ya existe.'
                    : $ex->getMessage();

                return redirect()->back()
                    ->withErrors(['error' => $mensajeError])
                    ->withInput();
            } catch (\Exception $ex) {
                return redirect()->back()
                    ->withErrors(['error' => $ex->getMessage()])
                    ->withInput();
            }
        }
        // Si solo puede modificar el envío
        elseif ($request->user()->can('unidad-modificar-envio')) {
            $rules = [
                'envio' => 'nullable|string|max:255', // ahora no es obligatorio
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            try {
                // Solo actualizamos envio si se mandó en el request
                if ($request->has('envio')) {
                    $unidad->envio = $request->input('envio');
                    $unidad->save();
                }
            } catch (\Exception $ex) {
                return redirect()->back()
                    ->withErrors(['error' => $ex->getMessage()])
                    ->withInput();
            }
        }
        else {
            abort(403, 'No tienes permisos para modificar esta unidad.');
        }

        return redirect()->route('unidads.index')
            ->with('success', 'Unidad modificada con éxito');
    }




    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $unidad = Unidad::find($id);

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
        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('unidads.show', compact('unidad','productos','sucursals'));

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Unidad::find($id)->delete();

        return redirect()->route('unidads.index')
            ->with('success','Unidad eliminada con éxito');
    }

    public function getUnidadsPorProducto($productoId)
    {

        $sucursalOrigenId = request('sucursal_origen_id');

        // Si no envían sucursal_origen_id, devolver array vacío
        if (!$sucursalOrigenId) {
            return response()->json([]);
        }

        $unidades = Unidad::where('producto_id', $productoId)
            ->where('sucursal_id', $sucursalOrigenId)
            ->get();

        // Retornar JSON con id y texto formateado
        return response()->json(
            $unidades->map(function ($unidad) {
                return [
                    'id' => $unidad->id,
                    'texto' => 'Motor: ' . $unidad->motor . ' - Cuadro: ' . $unidad->cuadro,
                ];
            })
        );
    }

    public function exportarXLS(Request $request)
    {
        $columnas = ['tipo_unidads.nombre','marcas.nombre','modelos.nombre','colors.nombre','sucursals.nombre','unidads.ingreso','unidads.year','unidads.envio','unidads.motor','unidads.cuadro']; // Define las columnas disponibles

        $busqueda = $request->search;

        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = Unidad::select('unidads.id as id', 'tipo_unidads.nombre as tipo_unidad_nombre', 'marcas.nombre as marca_nombre', 'modelos.nombre as modelo_nombre', 'colors.nombre as color_nombre','sucursals.nombre as sucursal_nombre','unidads.ingreso','unidads.year','unidads.envio','unidads.motor','unidads.cuadro')
            ->leftJoin('productos', 'unidads.producto_id', '=', 'productos.id')
            ->leftJoin('sucursals', 'unidads.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('tipo_unidads', 'productos.tipo_unidad_id', '=', 'tipo_unidads.id')
            ->leftJoin('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('colors', 'productos.color_id', '=', 'colors.id');


        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $unidads = $query->get();

        // ===============================
        //     📄 CREAR ARCHIVO XLSX
        // ===============================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Unidades");

        // ------------------------------
        // FILTROS
        // ------------------------------

        $sheet->setCellValue('A3', 'Búsqueda:');
        $sheet->setCellValue('B3', $busqueda ?: '—');

        // Espacio antes de la tabla
        $startRow = 5;

        // ------------------------------
        // ENCABEZADOS DE LA TABLA
        // ------------------------------
        $headers = [
            "Tipo", "Marca", "Modelo", "Color",
            "Sucursal", "Ingreso", "Año", "Envío", "Motor", "Envío", "Cuadro"
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

        foreach ($unidads as $p) {
            $sheet->setCellValue("A{$row}", $p->tipo_unidad_nombre);
            $sheet->setCellValue("B{$row}", $p->marca_nombre);
            $sheet->setCellValue("C{$row}", $p->modelo_nombre);
            $sheet->setCellValue("D{$row}", $p->color_nombre);
            $sheet->setCellValue("E{$row}", $p->sucursal_nombre);
            // 🟢 Formato de fecha dd/mm/YYYY
            $sheet->setCellValue("G{$row}",
                $p->ingreso
                    ? \Carbon\Carbon::parse($p->ingreso)->format('d/m/Y')
                    : '—'
            );
            $sheet->setCellValue("F{$row}", $p->year);

            $sheet->setCellValue("H{$row}", $p->envio);
            $sheet->setCellValue("I{$row}", $p->motor);
            $sheet->setCellValue("J{$row}", $p->cuadro);
            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "unidads.xlsx";
        $filePath = tempnam(sys_get_temp_dir(), $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }






    public function exportarPDF(Request $request)
    {
        ini_set('memory_limit', '-1'); // ilimitado
        ini_set('max_execution_time', 0);

        $columnas = ['tipo_unidads.nombre','marcas.nombre','modelos.nombre','colors.nombre','sucursals.nombre','unidads.ingreso','unidads.year','unidads.envio','unidads.motor','unidads.cuadro']; // Define las columnas disponibles

        $busqueda = $request->search;

        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = Unidad::select('unidads.id as id', 'tipo_unidads.nombre as tipo_unidad_nombre', 'marcas.nombre as marca_nombre', 'modelos.nombre as modelo_nombre', 'colors.nombre as color_nombre','sucursals.nombre as sucursal_nombre','unidads.ingreso','unidads.year','unidads.envio','unidads.motor','unidads.cuadro')
            ->leftJoin('productos', 'unidads.producto_id', '=', 'productos.id')
            ->leftJoin('sucursals', 'unidads.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('tipo_unidads', 'productos.tipo_unidad_id', '=', 'tipo_unidads.id')
            ->leftJoin('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('colors', 'productos.color_id', '=', 'colors.id');


        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $unidads = $query->get();

        // Pasamos datos a la vista PDF
        $data = [
            'unidads' => $unidads,
            'busqueda' => $busqueda,
        ];

        $pdf = PDF::loadView('unidads.pdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('unidads.pdf');
    }

}
