<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Pieza;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $columnas = [   'pedidos.fecha','piezas.codigo as pieza_codigo','pedidos.nombre as nueva','pedidos.observacion','pedidos.estado']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Pedido::select('pedidos.fecha','piezas.codigo as pieza_codigo','pedidos.nombre as nueva','pedidos.observacion','pedidos.estado')

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
        return view('pedidos.show',compact('pedido'));
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

}

