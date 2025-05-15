<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Movimiento;
use App\Models\UnidadMovimiento;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;

class MovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:movimiento-listar|movimiento-crear|movimiento-editar|movimiento-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:movimiento-crear', ['only' => ['create','store']]);
        $this->middleware('permission:movimiento-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:movimiento-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $movimientos = Movimiento::all();
        return view ('movimientos.index',compact('movimientos'));
    }


    public function dataTable(Request $request)
    {
        $columnas = ['users.name','origen.nombre as origen_nombre','destino.nombre as destino_nombre','movimientos.fecha']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Movimiento::select('movimientos.id as id', 'users.name','origen.nombre as origen_nombre','destino.nombre as destino_nombre','movimientos.fecha')
            ->leftJoin('sucursals as origen', 'movimientos.sucursal_origen_id', '=', 'origen.id')
            ->leftJoin('sucursals as destino', 'movimientos.sucursal_destino_id', '=', 'destino.id')
            ->leftJoin('users', 'movimientos.user_id', '=', 'users.id')
            ;

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
        $recordsTotal = Movimiento::count();



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


        $productos = Producto::with(['tipoUnidad', 'marca', 'modelo', 'color'])
            ->get()
            ->mapWithKeys(function ($producto) {
                $texto = ($producto->tipoUnidad->nombre ?? '') . ' - '
                    . ($producto->marca->nombre ?? '') . ' - '
                    . ($producto->modelo->nombre ?? '') . ' - '
                    . ($producto->color->nombre ?? '');

                return [$producto->id => $texto];
            })
            ->prepend('', ''); // si necesitas un vacío al principio
        $origens = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $destinos = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('movimientos.create', compact('productos','origens','destinos'));
    }

    public function store(Request $request)
    {
        $rules = [
            'sucursal_origen_id' => 'required',
            'sucursal_destino_id' => 'required',
            'fecha' => 'required|date',
            'unidad_id' => 'required|array|min:1',
            'unidad_id.*' => 'required|distinct',
        ];


        // Definir los mensajes de error personalizados
        $messages = [
            'sucursal_origen_id.required' => 'El campo Origen es obligatorio.',
            'sucursal_destino_id.required' => 'El campo Destino es obligatorio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'unidad_id.required' => 'Debe agregar al menos una unidad.',
            'unidad_id.min' => 'Debe agregar al menos una unidad.',
            'unidad_id.*.required' => 'Debe seleccionar una unidad para cada producto.',
            'unidad_id.*.distinct' => 'No puede repetir unidades.',
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
        // Obtener el ID del usuario autenticado
        $userId = Auth::id();
        $input['user_id'] = $userId;

        DB::beginTransaction();
        $ok=1;
        try {
            $movimiento = Movimiento::create($input);

            $lastid=$movimiento->id;
            if(count($request->unidad_id) > 0)
            {
                foreach($request->unidad_id as $item=>$v){

                    $data2=array(
                        'movimiento_id'=>$lastid,
                        'unidad_id'=>$request->unidad_id[$item]
                    );
                    try {
                        UnidadMovimiento::create($data2);
                    }catch(QueryException $ex){
                        $error = $ex->getMessage();
                        $ok=0;
                        continue;
                    }
                }
            }

        }catch(Exception $e){
            //if email or phone exist before in db redirect with error messages
            $ok=0;
        }
        if ($ok){
            DB::commit();
            $respuestaID='success';
            $respuestaMSJ='Registro creado satisfactoriamente';
        }
        else{
            DB::rollback();
            $respuestaID='error';
            $respuestaMSJ=$error;
        }

        return redirect()->route('movimientos.index')->with($respuestaID,$respuestaMSJ);



    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $movimiento = Movimiento::find($id);

        $productos = Producto::with(['tipoMovimiento', 'marca', 'modelo', 'color'])
            ->get()
            ->mapWithKeys(function ($producto) {
                $texto = ($producto->tipoMovimiento->nombre ?? '') . ' - '
                    . ($producto->marca->nombre ?? '') . ' - '
                    . ($producto->modelo->nombre ?? '') . ' - '
                    . ($producto->color->nombre ?? '');

                return [$producto->id => $texto];
            })
            ->prepend('', ''); // si necesitas un vacío al principio
        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('movimientos.edit', compact('movimiento','productos','sucursals'));

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
            'producto_id' => 'required',
            'sucursal_id' => 'required',
            'motor' => 'required',
            'cuadro' => 'required',
            'precio' => 'nullable|numeric', // puede ser vacío, o un número (decimal)
            'minimo' => 'nullable|integer', // puede ser vacío, o un entero

        ];

        // Definir los mensajes de error personalizados
        $messages = [

            'producto_id.required' => 'El campo Producto es obligatorio.',
            'sucursal_id.required' => 'El campo Sucursal es obligatorio.',
            'motor.required' => 'El campo Nro. Motor es obligatorio.',
            'cuadro.required' => 'El campo Nro. Cuadro es obligatorio.',
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




        $movimiento = Movimiento::find($id);
        try {
            $movimiento->update($input);

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

        return redirect()->route('movimientos.index')
            ->with('success','Movimiento modificada con éxito');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Movimiento::find($id)->delete();

        return redirect()->route('movimientos.index')
            ->with('success','Movimiento eliminada con éxito');
    }
}
