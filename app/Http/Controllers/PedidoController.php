<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Pieza;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;
class PedidoController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:pedido-listar|pedido-crear|pedido-editar|pedido-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:pedido-crear', ['only' => ['create','store']]);
        $this->middleware('permission:pedido-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:pedido-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $pedidos = Pedido::all();
        return view ('pedidos.index',compact('pedidos'));
    }


    public function dataTable(Request $request)
    {
        $columnas = [   'pedidos.fecha','piezas.codigo','pedidos.nombre','pedidos.observacion','pedidos.estado','pedidos.id']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Pedido::select('pedidos.id as id','pedidos.fecha','piezas.codigo as pieza_codigo','pedidos.nombre as nueva','pedidos.observacion','pedidos.estado')

            ->leftJoin('piezas', 'pedidos.pieza_id', '=', 'piezas.id')
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
        $recordsTotal = Pedido::count();



        return response()->json([
            'data' => $datos, // Obtener solo los elementos paginados
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
        ]);
    }


    public function create()
    {

        $piezas = Pieza::orderBy('codigo')->pluck('codigo', 'id')->prepend('', '');


        return view('pedidos.create', compact('piezas'));
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
            'pieza_id' => 'required_without:nombre|nullable|integer|exists:piezas,id',
            'nombre'   => 'required_without:pieza_id|nullable|string|max:255',
            'fecha'    => 'required|date',
            'cantidad'    => 'required|numeric',
        ], [
            'pieza_id.required_without' => 'Debe seleccionar una pieza existente o ingresar una nueva.',
            'nombre.required_without'   => 'Debe ingresar una nueva pieza o seleccionar una existente.',
        ]);


        $input = $this->sanitizeInput($request->all());


        $pedido = Pedido::create($input);


        return redirect()->route('pedidos.index')
            ->with('success','Pedido creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pedido = Pedido::find($id);
		$piezas = Pieza::orderBy('codigo')->pluck('codigo', 'id')->prepend('', '');
        return view('pedidos.show',compact('pedido','piezas'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pedido = Pedido::find($id);

        $piezas = Pieza::orderBy('codigo')->pluck('codigo', 'id')->prepend('', '');
        return view('pedidos.edit',compact('pedido','piezas'));
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
        $this->validate($request, [
            'pieza_id' => 'required_without:nombre|nullable|integer|exists:piezas,id',
            'nombre'   => 'required_without:pieza_id|nullable|string|max:255',
            'fecha'    => 'required|date',
            'cantidad'    => 'required|numeric',
        ], [
            'pieza_id.required_without' => 'Debe seleccionar una pieza existente o ingresar una nueva.',
            'nombre.required_without'   => 'Debe ingresar una nueva pieza o seleccionar una existente.',
        ]);


        $input = $this->sanitizeInput($request->all());




        $pedido = Pedido::find($id);
        $pedido->update($input);



        return redirect()->route('pedidos.index')
            ->with('success','Pedido modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Pedido::find($id)->delete();

        return redirect()->route('pedidos.index')
            ->with('success','Pedido eliminado con éxito');
    }

    public function exportarXLS(Request $request)
    {
        $columnas = [   'pedidos.fecha','piezas.codigo','pedidos.nombre','pedidos.observacion','pedidos.estado','pedidos.id']; // Define las columnas disponibles

        $busqueda = $request->search;

        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
        $query = Pedido::select('pedidos.id as id','pedidos.fecha','piezas.codigo as pieza_codigo','pedidos.nombre as nueva','pedidos.observacion','pedidos.estado')

            ->leftJoin('piezas', 'pedidos.pieza_id', '=', 'piezas.id');

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $pedidos = $query->get();

        // ===============================
        //     📄 CREAR ARCHIVO XLSX
        // ===============================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Pedidos");

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
            "Fecha", "Pieza", "Nueva", "Observaciones",
            "Estado"
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

        foreach ($pedidos as $p) {
            $sheet->setCellValue("A{$row}",
                $p->fecha
                    ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y')
                    : '—'
            );
            $sheet->setCellValue("B{$row}", $p->pieza_codigo);
            $sheet->setCellValue("C{$row}", $p->nueva);
            $sheet->setCellValue("D{$row}", $p->observacion);
            $sheet->setCellValue("E{$row}", $p->estado);

            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "pedidos.xlsx";
        $filePath = tempnam(sys_get_temp_dir(), $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }






    public function exportarPDF(Request $request)
    {
        ini_set('memory_limit', '-1'); // ilimitado
        ini_set('max_execution_time', 0);

        $columnas = [   'pedidos.fecha','piezas.codigo','pedidos.nombre','pedidos.observacion','pedidos.estado','pedidos.id']; // Define las columnas disponibles



        $busqueda = $request->search;


        // MISMA QUERY QUE EN dataTable
        $query = Pedido::select('pedidos.id as id','pedidos.fecha','piezas.codigo as pieza_codigo','pedidos.nombre as nueva','pedidos.observacion','pedidos.estado')

            ->leftJoin('piezas', 'pedidos.pieza_id', '=', 'piezas.id');

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $pedidos = $query->get();

        // Pasamos datos a la vista PDF
        $data = [
            'pedidos' => $pedidos,
            'busqueda' => $busqueda,
        ];

        $pdf = PDF::loadView('pedidos.pdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('pedidos.pdf');
    }

}

