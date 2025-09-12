<?php

namespace App\Http\Controllers;

use App\Models\Concepto;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;

class ConceptoController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:concepto-listar|concepto-crear|concepto-editar|concepto-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:concepto-crear', ['only' => ['create','store']]);
        $this->middleware('permission:concepto-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:concepto-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $conceptos = Concepto::all();

        return view ('conceptos.index',compact('conceptos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('conceptos.create');
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
            'tipo' => 'required'
        ]);


        $input = $this->sanitizeInput($request->all());


        $concepto = Concepto::create($input);



        return redirect()->route('conceptos.index')
            ->with('success','Concepto creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $concepto = Concepto::find($id);
        return view('conceptos.show',compact('concepto'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $concepto = Concepto::find($id);


        return view('conceptos.edit',compact('concepto'));
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
            'tipo' => 'required'

        ]);

        $input = $this->sanitizeInput($request->all());




        $concepto = Concepto::find($id);
        $concepto->update($input);



        return redirect()->route('conceptos.index')
            ->with('success','Concepto modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Concepto::find($id)->delete();

        return redirect()->route('conceptos.index')
            ->with('success','Concepto eliminado con éxito');
    }
}
