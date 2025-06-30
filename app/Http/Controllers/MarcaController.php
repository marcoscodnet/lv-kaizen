<?php

namespace App\Http\Controllers;

use App\Models\TipoUnidad;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Marca;
class MarcaController extends Controller
{
    use SanitizesInput;
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
    function __construct()
    {
        $this->middleware('permission:marca-listar|marca-crear|marca-editar|marca-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:marca-crear', ['only' => ['create','store']]);
        $this->middleware('permission:marca-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:marca-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $marcas = Marca::all();

        return view ('marcas.index',compact('marcas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tipoUnidads = TipoUnidad::all();
        return view('marcas.create',compact('tipoUnidads'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required'

        ]);


        $input = $this->sanitizeInput($request->all());


        $marca = Marca::create($input);

        // Sincronizar los tipos de unidades seleccionados
        if ($request->has('tipos')) {
            $marca->tipoUnidads()->sync($request->input('tipos'));
        }

        return redirect()->route('marcas.index')
            ->with('success','Marca creada con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = Marca::find($id);
        return view('marcas.show',compact('marca'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $marca = Marca::find($id);

        $tipoUnidads = TipoUnidad::all();
        return view('marcas.edit',compact('marca','tipoUnidads'));
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
            'nombre' => 'required'

        ]);

        $input = $this->sanitizeInput($request->all());




        $marca = Marca::find($id);
        $marca->update($input);

// Sincronizar los tipos de unidades seleccionados
        if ($request->has('tipos')) {
            $marca->tipoUnidads()->sync($request->input('tipos'));
        }

        return redirect()->route('marcas.index')
            ->with('success','Marca modificada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Marca::find($id)->delete();

        return redirect()->route('marcas.index')
            ->with('success','Marca eliminada con éxito');
    }
}
