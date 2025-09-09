<?php

namespace App\Http\Controllers;

use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\TipoPieza;
class TipoPiezaController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:tipo-pieza-listar|tipo-pieza-crear|tipo-pieza-editar|tipo-pieza-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:tipo-pieza-crear', ['only' => ['create','store']]);
        $this->middleware('permission:tipo-pieza-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:tipo-pieza-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $tipoPiezas = TipoPieza::all();
        return view ('tipoPiezas.index',compact('tipoPiezas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('tipoPiezas.create');
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


        $tipoPieza = TipoPieza::create($input);


        return redirect()->route('tipoPiezas.index')
            ->with('success','Tipo de Pieza creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tipoPieza = TipoPieza::find($id);
        return view('tipoPiezas.show',compact('tipoPieza'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tipoPieza = TipoPieza::find($id);


        return view('tipoPiezas.edit',compact('tipoPieza'));
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




        $tipoPieza = TipoPieza::find($id);
        $tipoPieza->update($input);



        return redirect()->route('tipoPiezas.index')
            ->with('success','Tipo de Pieza modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        TipoPieza::find($id)->delete();

        return redirect()->route('tipoPiezas.index')
            ->with('success','Tipo de Pieza eliminado con éxito');
    }
}
