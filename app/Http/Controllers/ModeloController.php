<?php

namespace App\Http\Controllers;

use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Modelo;
use App\Models\Marca;
class ModeloController extends Controller
{
    use SanitizesInput;
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
    function __construct()
    {
        $this->middleware('permission:modelo-listar|modelo-crear|modelo-editar|modelo-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:modelo-crear', ['only' => ['create','store']]);
        $this->middleware('permission:modelo-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:modelo-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $modelos = Modelo::all();
        return view ('modelos.index',compact('modelos'));
    }

    public function dataTable(Request $request)
    {
        $columnas = ['modelos.nombre', 'marcas.nombre']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Modelo::select('modelos.id as id', 'modelos.nombre as modelo_nombre', 'marcas.nombre as marca_nombre')

            ->leftJoin('marcas', 'modelos.marca_id', '=', 'marcas.id')
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
        $recordsTotal = Modelo::count();



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

        $marcas = Marca::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('modelos.create', compact('marcas'));
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
            'nombre' => 'required',
            'marca_id' => 'required'

        ]);


        $input = $this->sanitizeInput($request->all());


        $modelo = Modelo::create($input);


        return redirect()->route('modelos.index')
            ->with('success','Modelo creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = Modelo::find($id);
        return view('modelos.show',compact('modelo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $modelo = Modelo::find($id);

        $marcas = Marca::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('modelos.edit',compact('modelo','marcas'));
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
            'nombre' => 'required',
            'marca_id' => 'required'

        ]);

        $input = $this->sanitizeInput($request->all());




        $modelo = Modelo::find($id);
        $modelo->update($input);



        return redirect()->route('modelos.index')
            ->with('success','Modelo modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Modelo::find($id)->delete();

        return redirect()->route('modelos.index')
            ->with('success','Modelo eliminado con éxito');
    }
}
