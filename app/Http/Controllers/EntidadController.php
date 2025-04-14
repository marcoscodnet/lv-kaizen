<?php

namespace App\Http\Controllers;

use App\Models\TipoUnidad;
use Illuminate\Http\Request;
use App\Models\Entidad;
class EntidadController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
    function __construct()
    {
        $this->middleware('permission:entidad-listar|entidad-crear|entidad-editar|entidad-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:entidad-crear', ['only' => ['create','store']]);
        $this->middleware('permission:entidad-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:entidad-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $entidads = Entidad::all();

        return view ('entidads.index',compact('entidads'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('entidads.create');
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


        $input = $request->all();


        $entidad = Entidad::create($input);



        return redirect()->route('entidads.index')
            ->with('success','Entidad creada con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $entidad = Entidad::find($id);
        return view('entidads.show',compact('entidad'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $entidad = Entidad::find($id);


        return view('entidads.edit',compact('entidad'));
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

        $input = $request->all();




        $entidad = Entidad::find($id);
        $entidad->update($input);



        return redirect()->route('entidads.index')
            ->with('success','Entidad modificada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Entidad::find($id)->delete();

        return redirect()->route('entidads.index')
            ->with('success','Entidad eliminada con éxito');
    }
}
