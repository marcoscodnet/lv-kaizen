<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
class SucursalController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
    function __construct()
    {
        $this->middleware('permission:sucursal-listar|sucursal-crear|sucursal-editar|sucursal-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:sucursal-crear', ['only' => ['create','store']]);
        $this->middleware('permission:sucursal-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:sucursal-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $sucursals = Sucursal::all();
        return view ('sucursals.index',compact('sucursals'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('sucursals.create');
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
            'nombre' => 'required',
            'telefono' => 'required',
            'localidad' => 'required'

        ]);


        $input = $request->all();


        $sucursal = Sucursal::create($input);


        return redirect()->route('sucursals.index')
            ->with('success','Sucursal creada con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sucursal = Sucursal::find($id);
        return view('sucursals.show',compact('sucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sucursal = Sucursal::find($id);


        return view('sucursals.edit',compact('sucursal'));
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
            'telefono' => 'required',
            'localidad' => 'required'

        ]);

        $input = $request->all();




        $sucursal = Sucursal::find($id);
        $sucursal->update($input);



        return redirect()->route('sucursals.index')
            ->with('success','Sucursal modificada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Sucursal::find($id)->delete();

        return redirect()->route('sucursals.index')
            ->with('success','Sucursal eliminada con éxito');
    }
}
