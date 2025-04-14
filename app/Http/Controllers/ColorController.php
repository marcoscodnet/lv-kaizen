<?php

namespace App\Http\Controllers;

use App\Models\TipoUnidad;
use Illuminate\Http\Request;
use App\Models\Color;
class ColorController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
    function __construct()
    {
        $this->middleware('permission:color-listar|color-crear|color-editar|color-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:color-crear', ['only' => ['create','store']]);
        $this->middleware('permission:color-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:color-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $colors = Color::all();

        return view ('colors.index',compact('colors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('colors.create');
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


        $color = Color::create($input);



        return redirect()->route('colors.index')
            ->with('success','Color creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $color = Color::find($id);
        return view('colors.show',compact('color'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $color = Color::find($id);


        return view('colors.edit',compact('color'));
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




        $color = Color::find($id);
        $color->update($input);



        return redirect()->route('colors.index')
            ->with('success','Color modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Color::find($id)->delete();

        return redirect()->route('colors.index')
            ->with('success','Color eliminado con éxito');
    }
}
