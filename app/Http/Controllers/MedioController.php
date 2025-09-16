<?php

namespace App\Http\Controllers;

use App\Models\Medio;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;

class MedioController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:medio-listar|medio-crear|medio-editar|medio-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:medio-crear', ['only' => ['create','store']]);
        $this->middleware('permission:medio-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:medio-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $medios = Medio::all();

        return view ('medios.index',compact('medios'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('medios.create');
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


        $medio = Medio::create($input);



        return redirect()->route('medios.index')
            ->with('success','Medio creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $medio = Medio::find($id);
        return view('medios.show',compact('medio'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $medio = Medio::find($id);


        return view('medios.edit',compact('medio'));
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




        $medio = Medio::find($id);
        $medio->update($input);



        return redirect()->route('medios.index')
            ->with('success','Medio modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Medio::find($id)->delete();

        return redirect()->route('medios.index')
            ->with('success','Medio eliminado con éxito');
    }
}
