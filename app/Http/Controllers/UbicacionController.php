<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Ubicacion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class UbicacionController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:ubicacion-listar|ubicacion-crear|ubicacion-editar|ubicacion-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:ubicacion-crear', ['only' => ['create','store']]);
        $this->middleware('permission:ubicacion-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:ubicacion-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $ubicacions = Ubicacion::all();
        return view ('ubicacions.index',compact('ubicacions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('ubicacions.create', compact('sucursals'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'nombre' => [
                'required',
                Rule::unique('ubicacions')
                    ->where(function ($query) use ($request) {
                        return $query->where('sucursal_id', $request->sucursal_id);
                    }),
            ],
            'sucursal_id' => 'required',
        ];

        // Definir los mensajes de error personalizados
        $messages = [
            'sucursal_id.required' => 'El campo Sucursal es obligatorio.',
            'nombre.unique' => 'Ya existe una ubicación con ese nombre para la sucursal seleccionada.',
        ];

        // Crear el validador con las reglas y mensajes
        $validator = Validator::make($request->all(), $rules, $messages);

        // Validar y verificar si hay errores
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $this->sanitizeInput($request->all());
        $ubicacion = Ubicacion::create($input);
        return redirect()->route('ubicacions.index')
            ->with('success','Ubicación creada con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ubicacion = Ubicacion::find($id);
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('ubicacions.show',compact('ubicacion','sucursals'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $ubicacion = Ubicacion::find($id);
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('ubicacions.edit',compact('ubicacion','sucursals'));
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
        $rules = [
            'nombre' => [
                'required',
                Rule::unique('ubicacions')
                    ->where(function ($query) use ($request) {
                        return $query->where('sucursal_id', $request->sucursal_id);
                    })
                    ->ignore($id),
            ],
            'sucursal_id' => 'required',
        ];

        // Definir los mensajes de error personalizados
        $messages = [
            'sucursal_id.required' => 'El campo Sucursal es obligatorio.',
            'nombre.unique' => 'Ya existe una ubicación con ese nombre para la sucursal seleccionada.',
        ];

        // Crear el validador con las reglas y mensajes
        $validator = Validator::make($request->all(), $rules, $messages);

        // Validar y verificar si hay errores
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $this->sanitizeInput($request->all());

        $ubicacion = Ubicacion::find($id);
        $ubicacion->update($input);

        return redirect()->route('ubicacions.index')
            ->with('success','Ubicación modificada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Ubicacion::find($id)->delete();

        return redirect()->route('ubicacions.index')
            ->with('success','Ubicación eliminada con éxito');
    }
}
