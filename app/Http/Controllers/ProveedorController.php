<?php

namespace App\Http\Controllers;

use App\Models\Provincia;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;
class ProveedorController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:proveedor-listar|proveedor-crear|proveedor-editar|proveedor-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:proveedor-crear', ['only' => ['create','store']]);
        $this->middleware('permission:proveedor-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:proveedor-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $proveedors = Proveedor::all();
        return view ('proveedors.index',compact('proveedors'));
    }


    public function dataTable(Request $request)
    {
        $columnas = ['proveedors.nombre','proveedors.razon','proveedors.particular','proveedors.celular','proveedors.email']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Proveedor::select('proveedors.id as id', 'proveedors.nombre as proveedor_nombre','proveedors.razon', DB::raw("CONCAT('(',proveedors.particular_area, ') ', proveedors.particular) as telefono"), DB::raw("CONCAT('(',proveedors.celular_area, ') ', proveedors.celular) as celular"),'proveedors.email')
        ;

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
        $recordsTotal = Proveedor::count();



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


        return view('proveedors.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'nombre' => 'required',
            'razon' => 'required',
            'cuil' => 'regex:/^\d{2}-\d{8}-\d{1}$/',

            'particular_area' => 'required',
            'particular' => 'required',
            'celular_area' => 'required',
            'celular' => 'required',
            'email' => [
                'required',
                'email',
                'regex:/^[^@]+@[^@]+\.[^@]+$/i'
            ],
            'iva' => 'required',
        ];

        // Definir los mensajes de error personalizados
        $messages = [
            'cuil.regex' => 'El formato del CUIL es inválido.',
            'particular_area.required' => 'El campo Área del teléfono particular es obligatorio.',
            'celular_area.required' => 'El campo Área del teléfono celular es obligatorio.',
            'iva.required' => 'El campo Condivión IVA es obligatorio.',
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


        $proveedor = Proveedor::create($input);


        return redirect()->route('proveedors.index')
            ->with('success','Proveedor creado con éxito');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $proveedor = Proveedor::find($id);


        return view('proveedors.edit',compact('proveedor'));
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
            'nombre' => 'required',
            'razon' => 'required',
            'cuil' => 'regex:/^\d{2}-\d{8}-\d{1}$/',

            'particular_area' => 'required',
            'particular' => 'required',
            'celular_area' => 'required',
            'celular' => 'required',
            'iva' => 'required',
        ];

        // Definir los mensajes de error personalizados
        $messages = [
            'cuil.regex' => 'El formato del CUIL es inválido.',
            'particular_area.required' => 'El campo Área del teléfono particular es obligatorio.',
            'celular_area.required' => 'El campo Área del teléfono celular es obligatorio.',
            'iva.required' => 'El campo Condivión IVA es obligatorio.',
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




        $proveedor = Proveedor::find($id);
        try {
            $proveedor->update($input);

        } catch (QueryException $ex) {

            if ($ex->errorInfo[1] == 1062) {
                $mensajeError = 'El Proveedor ya existe.';
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

        return redirect()->route('proveedors.index')
            ->with('success','Proveedor modificado con éxito');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Proveedor::find($id)->delete();

        return redirect()->route('proveedors.index')
            ->with('success','Proveedor eliminado con éxito');
    }

    public function show($id)
    {
        $proveedor = Proveedor::find($id);


        return view('proveedors.show',compact('proveedor'));
    }



    public function quickStore(Request $request)
    {
        //dd($request);
        // Validación básica
        $rules = [
            'nombre' => 'required',
            'razon' => 'required',
            'cuil' => 'nullable|regex:/^\d{2}-\d{8}-\d{1}$/',

            'particular_area' => 'required',
            'particular' => 'required',
            'celular_area' => 'required',
            'celular' => 'required',
            'iva' => 'required',
        ];

        $messages = [
            'cuil.regex' => 'El formato del CUIL es inválido.',
            'particular_area.required' => 'El campo Área del teléfono particular es obligatorio.',
            'celular_area.required' => 'El campo Área del teléfono celular es obligatorio.',
            'iva.required' => 'El campo Condición IVA es obligatorio.',
        ];



        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $input = $this->sanitizeInput($request->all());

        // Crear o actualizar proveedor
        if ($request->has('proveedor_id') && $proveedor = Proveedor::find($request->proveedor_id)) {
            $proveedor->update($input);
        } else {
            $proveedor = Proveedor::create($input);
        }

        return response()->json([
            'id' => $proveedor->id,
            'text' => $proveedor->full_name_phone
        ]);
    }

    public function showJson($id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json(['error' => 'Proveedor no encontrado'], 404);
        }

        return response()->json($proveedor);
    }

    public function exportarXLS(Request $request)
    {
        $columnas = ['proveedors.nombre', 'proveedors.razon','proveedors.particular','proveedors.celular','proveedors.email']; // Define las columnas disponibles

        $busqueda = $request->search;

        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = Proveedor::select('proveedors.id as id', 'proveedors.nombre as proveedor_nombre','proveedors.razon', DB::raw("CONCAT('(',proveedors.particular_area, ') ', proveedors.particular) as telefono"), DB::raw("CONCAT('(',proveedors.celular_area, ') ', proveedors.celular) as celular"),'proveedors.email')
        ;

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $proveedors = $query->get();

        // ===============================
        //     📄 CREAR ARCHIVO XLSX
        // ===============================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Proveedores");

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
            "Nombre", "Razón Social", "Teléfono", "Celular",
            "Localidad", "E-mail"
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

        foreach ($proveedors as $p) {
            $sheet->setCellValue("A{$row}", $p->proveedor_nombre);
            $sheet->setCellValue("B{$row}", $p->razon);
            $sheet->setCellValue("C{$row}", $p->telefono);
            $sheet->setCellValue("D{$row}", $p->celular);

            $sheet->setCellValue("E{$row}", $p->email);
            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "proveedores.xlsx";
        $filePath = tempnam(sys_get_temp_dir(), $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }






    public function exportarPDF(Request $request)
    {
        ini_set('memory_limit', '-1'); // ilimitado
        ini_set('max_execution_time', 0);

        $columnas = ['proveedors.nombre', 'proveedors.razon','proveedors.particular','proveedors.celular','proveedors.email']; // Define las columnas disponibles


        $busqueda = $request->search;


        // MISMA QUERY QUE EN dataTable
        $query = Proveedor::select('proveedors.id as id', 'proveedors.nombre as proveedor_nombre','proveedors.razon', DB::raw("CONCAT('(',proveedors.particular_area, ') ', proveedors.particular) as telefono"), DB::raw("CONCAT('(',proveedors.celular_area, ') ', proveedors.celular) as celular"),'proveedors.email')
        ;

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $proveedors = $query->get();

        // Pasamos datos a la vista PDF
        $data = [
            'proveedors' => $proveedors,
            'busqueda' => $busqueda,
        ];

        $pdf = PDF::loadView('proveedors.pdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('proveedors.pdf');
    }




}
