<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoServicio;
class TipoServicioController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
    function __construct()
    {
        $this->middleware('permission:tipo-servicio-listar|tipo-servicio-crear|tipo-servicio-editar|tipo-servicio-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:tipo-servicio-crear', ['only' => ['create','store']]);
        $this->middleware('permission:tipo-servicio-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:tipo-servicio-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $tipoServicios = TipoServicio::all();
        return view ('tipoServicios.index',compact('tipoServicios'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('tipoServicios.create');
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


        $tipoServicio = TipoServicio::create($input);


        return redirect()->route('tipoServicios.index')
            ->with('success','Tipo de Servicio creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tipoServicio = TipoServicio::find($id);
        return view('tipoServicios.show',compact('tipoServicio'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tipoServicio = TipoServicio::find($id);


        return view('tipoServicios.edit',compact('tipoServicio'));
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




        $tipoServicio = TipoServicio::find($id);
        $tipoServicio->update($input);



        return redirect()->route('tipoServicios.index')
            ->with('success','Tipo de Servicio modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        TipoServicio::find($id)->delete();

        return redirect()->route('tipoServicios.index')
            ->with('success','Tipo de Servicio eliminado con éxito');
    }
}
