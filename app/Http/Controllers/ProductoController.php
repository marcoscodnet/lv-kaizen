<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Models\TipoUnidad;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Color;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:producto-listar|producto-crear|producto-editar|producto-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:producto-crear', ['only' => ['create','store']]);
        $this->middleware('permission:producto-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:producto-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $productos = Producto::all();
        return view ('productos.index',compact('productos'));
    }


    public function dataTable(Request $request)
    {
        $columnas = ['tipo_unidads.nombre','marcas.nombre','modelos.nombre','colors.nombre','productos.precio','productos.minimo',DB::raw("CASE WHEN productos.discontinuo = 1 THEN 'SI' ELSE 'NO' END")]; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Producto::select('productos.id as id', 'tipo_unidads.nombre as tipo_unidad_nombre', 'marcas.nombre as marca_nombre', 'modelos.nombre as modelo_nombre', 'colors.nombre as color_nombre','productos.precio','productos.minimo', DB::raw("CASE WHEN productos.discontinuo = 1 THEN 'SI' ELSE 'NO' END AS discontinuo"))

            ->leftJoin('tipo_unidads', 'productos.tipo_unidad_id', '=', 'tipo_unidads.id')
            ->leftJoin('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('colors', 'productos.color_id', '=', 'colors.id');

        // Aplicar la búsqueda
        if (!empty($busqueda)) {
            $query->where(function ($query) use ($columnas, $busqueda) {
                foreach ($columnas as $columna) {
                    if ($columna){
                        $query->orWhere($columna, 'like', "%$busqueda%");
                    }

                }
            });
        }




        // Obtener la cantidad total de registros después de aplicar el filtro de búsqueda
        $recordsFiltered = $query->count();


        $datos = $query->orderBy($columnaOrden, $orden)->skip($request->input('start'))->take($request->input('length'))->get();

        // Obtener la cantidad total de registros sin filtrar
        $recordsTotal = Producto::count();



        return response()->json([
            'data' => $datos, // Obtener solo los elementos paginados
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $tipoUnidads = TipoUnidad::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $modelos = Modelo::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $marcas = Marca::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $colors = Color::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('productos.create', compact('tipoUnidads','modelos','marcas','colors'));
    }

    public function store(Request $request)
    {
        $rules = [
            'tipo_unidad_id' => 'required',
            'marca_id' => 'required',
            'modelo_id' => 'required',
            'color_id' => 'required',
            'precio' => 'nullable|numeric', // puede ser vacío, o un número (decimal)
            'minimo' => 'nullable|integer', // puede ser vacío, o un entero

        ];

        // Definir los mensajes de error personalizados
        $messages = [

            'tipo_unidad_id.required' => 'El campo Tipo de Unidad es obligatorio.',
            'marca_id.required' => 'El campo Marca es obligatorio.',
            'modelo_id.required' => 'El campo Modelo es obligatorio.',
            'color_id.required' => 'El campo Color es obligatorio.',
        ];

        // Crear el validador con las reglas y mensajes
        $validator = Validator::make($request->all(), $rules, $messages);

        // Validar y verificar si hay errores
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $input = $request->all();


        $producto = Producto::create($input);


        return redirect()->route('productos.index')
            ->with('success','Producto creado con éxito');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $producto = Producto::find($id);

        $tipoUnidads = TipoUnidad::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $modelos = Modelo::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $marcas = Marca::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $colors = Color::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('productos.edit', compact('producto','tipoUnidads','modelos','marcas','colors'));

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
            'tipo_unidad_id' => 'required',
            'marca_id' => 'required',
            'modelo_id' => 'required',
            'color_id' => 'required',
            'precio' => 'nullable|numeric', // puede ser vacío, o un número (decimal)
            'minimo' => 'nullable|integer', // puede ser vacío, o un entero

        ];

        // Definir los mensajes de error personalizados
        $messages = [

            'tipo_unidad_id.required' => 'El campo Tipo de Unidad es obligatorio.',
            'marca_id.required' => 'El campo Marca es obligatorio.',
            'modelo_id.required' => 'El campo Modelo es obligatorio.',
            'color_id.required' => 'El campo Color es obligatorio.',
        ];

        // Crear el validador con las reglas y mensajes
        $validator = Validator::make($request->all(), $rules, $messages);

        // Validar y verificar si hay errores
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();




        $producto = Producto::find($id);
        try {
            $producto->update($input);

        } catch (QueryException $ex) {

            if ($ex->errorInfo[1] == 1062) {
                $mensajeError = 'El Producto ya existe.';
            } else {
                $mensajeError = $ex->getMessage();
            }

            // Retornar al formulario con error
            return redirect()->back()
                ->withErrors(['error' => $mensajeError])
                ->withInput();

        } catch (\Exception $ex) {

            return redirect()->back()
                ->withErrors(['error' => $ex->getMessage()])
                ->withInput();
        }

        return redirect()->route('productos.index')
            ->with('success','Producto modificado con éxito');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Producto::find($id)->delete();

        return redirect()->route('productos.index')
            ->with('success','Producto eliminado con éxito');
    }
}
