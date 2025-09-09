<?php

namespace App\Http\Controllers;

use App\Models\TipoPieza;
use App\Models\StockPieza;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Pieza;

class PiezaController extends Controller
{
    use SanitizesInput;
    function __construct()
    {
        $this->middleware('permission:pieza-listar|pieza-crear|pieza-editar|pieza-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:pieza-crear', ['only' => ['create','store']]);
        $this->middleware('permission:pieza-editar', ['only' => ['edit','update']]);
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
        return view ('piezas.index',compact('piezas'));
    }

    public function dataTable(Request $request)
    {
        $columnas = ['piezas.codigo', 'piezas.descripcion','tipo_piezas.nombre','piezas.stock_minimo','piezas.stock_actual','piezas.costo','piezas.precio_minimo','piezas.observaciones']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Pieza::select('piezas.id as id', 'piezas.codigo', 'piezas.descripcion','tipo_piezas.nombre as tipo_pieza','piezas.stock_minimo','piezas.stock_actual','piezas.costo','piezas.precio_minimo','piezas.observaciones')

            ->leftJoin('tipo_piezas', 'piezas.tipo_pieza_id', '=', 'tipo_piezas.id')
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
        $recordsTotal = Pieza::count();



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

        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        return view('piezas.create',compact('tipos'));
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


        $pieza = Pieza::create($input);


        return redirect()->route('piezas.index')
            ->with('success','Pieza creada con éxito');
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

        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        return view('piezas.edit',compact('pieza','tipos'));
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
            'codigo' => 'required',
            'descripcion' => 'required',
            'tipo_pieza_id' => 'required',
        ]);


        $input = $this->sanitizeInput($request->all());




        $pieza = Pieza::find($id);
        $pieza->update($input);



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


        Pieza::find($id)->delete();

        return redirect()->route('piezas.index')
            ->with('success','Pieza eliminada con éxito');
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

}
