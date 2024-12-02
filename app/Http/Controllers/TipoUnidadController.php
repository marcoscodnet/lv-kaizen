<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoUnidad;
class TipoUnidadController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
    function __construct()
    {
        $this->middleware('permission:tipo-unidad-listar|tipo-unidad-crear|tipo-unidad-editar|tipo-unidad-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:tipo-unidad-crear', ['only' => ['create','store']]);
        $this->middleware('permission:tipo-unidad-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:tipo-unidad-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $tipoUnidads = TipoUnidad::all();
        return view ('tipoUnidads.index',compact('tipoUnidads'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('tipoUnidads.create');
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


        $tipoUnidad = TipoUnidad::create($input);


        return redirect()->route('tipoUnidads.index')
            ->with('success','Tipo de Unidad creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tipoUnidad = TipoUnidad::find($id);
        return view('tipoUnidads.show',compact('tipoUnidad'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tipoUnidad = TipoUnidad::find($id);


        return view('tipoUnidads.edit',compact('tipoUnidad'));
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




        $tipoUnidad = TipoUnidad::find($id);
        $tipoUnidad->update($input);



        return redirect()->route('tipoUnidads.index')
            ->with('success','Tipo de Unidad modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        TipoUnidad::find($id)->delete();

        return redirect()->route('tipoUnidads.index')
            ->with('success','Tipo de Unidad eliminado con éxito');
    }
}
