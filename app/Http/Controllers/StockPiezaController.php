<?php

namespace App\Http\Controllers;

use App\Models\Pieza;
use App\Models\Sucursal;
use App\Models\StockPieza;
use App\Models\TipoPieza;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
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
        return view ('stockPiezas.index',compact('stockPiezas','sucursals'));
    }


    public function dataTable(Request $request)
    {
        $columnas = ['stock_piezas.remito','piezas.codigo','piezas.descripcion','stock_piezas.inicial','stock_piezas.cantidad','stock_piezas.costo','stock_piezas.precio_minimo','sucursals.nombre','stock_piezas.proveedor','stock_piezas.ingreso','stock_piezas.id']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');
        $sucursal_id = $request->input('sucursal_id');
        $query = StockPieza::select('stock_piezas.id as id','stock_piezas.remito','piezas.codigo','piezas.descripcion','stock_piezas.inicial','stock_piezas.cantidad','stock_piezas.costo','stock_piezas.precio_minimo','sucursals.nombre as sucursal_nombre','stock_piezas.proveedor','stock_piezas.ingreso')
            ->leftJoin('piezas', 'stock_piezas.pieza_id', '=', 'piezas.id')
            ->leftJoin('sucursals', 'stock_piezas.sucursal_id', '=', 'sucursals.id')
            ;

        if (!empty($sucursal_id)) {

            $request->session()->put('sucursal_filtro_pieza', $sucursal_id);

        }
        else{
            $sucursal_id = $request->session()->get('sucursal_filtro_pieza');

        }
        if ($sucursal_id=='-1'){
            $request->session()->forget('sucursal_filtro_pieza');
            $sucursal_id='';
        }
        if (!empty($sucursal_id)) {

            $query->where('stock_piezas.sucursal_id', $sucursal_id);


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
        return view('stockPiezas.create', compact('piezas','sucursals','tipos'));
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
        return view('stockPiezas.edit', compact('stockPieza','piezas','sucursals'));

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
        return view('stockPiezas.show', compact('stockPieza','piezas','sucursals'));

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
}
