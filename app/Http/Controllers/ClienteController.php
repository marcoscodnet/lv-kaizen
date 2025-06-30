<?php

namespace App\Http\Controllers;

use App\Models\Provincia;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:cliente-listar|cliente-crear|cliente-editar|cliente-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:cliente-crear', ['only' => ['create','store']]);
        $this->middleware('permission:cliente-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:cliente-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $clientes = Cliente::all();
        return view ('clientes.index',compact('clientes'));
    }


    public function dataTable(Request $request)
    {
        $columnas = ['clientes.nombre', 'clientes.documento','clientes.particular','clientes.celular','localidads.nombre','provincias.nombre','clientes.nacimiento','clientes.email']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Cliente::select('clientes.id as id', 'clientes.nombre as cliente_nombre','clientes.documento', DB::raw("CONCAT('(',clientes.particular_area, ') ', clientes.particular) as telefono"), DB::raw("CONCAT('(',clientes.celular_area, ') ', clientes.celular) as celular"), 'localidads.nombre as localidad_nombre', 'provincias.nombre as provincia_nombre','clientes.nacimiento','clientes.email')

            ->leftJoin('localidads', 'clientes.localidad_id', '=', 'localidads.id')
            ->leftJoin('provincias', 'localidads.provincia_id', '=', 'provincias.id')
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
        $recordsTotal = Cliente::count();



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

        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('clientes.create', compact('provincias'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nombre' => 'required',
            'documento' => 'required|unique:clientes,documento',
            'cuil' => 'regex:/^\d{2}-\d{8}-\d{1}$/',
            'nacimiento' => 'required|date',
            'particular_area' => 'required',
            'particular' => 'required',
            'celular_area' => 'required',
            'celular' => 'required',
            'calle' => 'required',
            'nro' => 'required',
            'cp' => 'required',
            'localidad_id' => 'required',
            'nacionalidad' => 'required',
            'estado_civil' => 'required',
            'llego' => 'required',
            'iva' => 'required',
        ];

        // Definir los mensajes de error personalizados
        $messages = [
            'cuil.regex' => 'El formato del CUIL es inválido.',
            'particular_area.required' => 'El campo Área del teléfono particular es obligatorio.',
            'celular_area.required' => 'El campo Área del teléfono celular es obligatorio.',
            'localidad_id.required' => 'El campo Localidad es obligatorio.',
            'llego.required' => 'El campo Como llegó? es obligatorio.',
            'iva.required' => 'El campo Condivión IVA es obligatorio.',
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


        $cliente = Cliente::create($input);


        return redirect()->route('clientes.index')
            ->with('success','Cliente creado con éxito');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cliente = Cliente::find($id);

        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('clientes.edit',compact('cliente','provincias'));
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
            'nombre' => 'required',
            'documento' => 'required|unique:clientes,documento,' . $id,
            'cuil' => 'regex:/^\d{2}-\d{8}-\d{1}$/',
            'nacimiento' => 'required',
            'particular_area' => 'required',
            'particular' => 'required',
            'celular_area' => 'required',
            'celular' => 'required',
            'calle' => 'required',
            'nro' => 'required',
            'cp' => 'required',
            'localidad_id' => 'required',
            'nacionalidad' => 'required',
            'estado_civil' => 'required',
            'llego' => 'required',
            'iva' => 'required',
        ];

        // Definir los mensajes de error personalizados
        $messages = [
            'cuil.regex' => 'El formato del CUIL es inválido.',
            'particular_area.required' => 'El campo Área del teléfono particular es obligatorio.',
            'celular_area.required' => 'El campo Área del teléfono celular es obligatorio.',
            'localidad_id.required' => 'El campo Localidad es obligatorio.',
            'llego.required' => 'El campo Como llegó? es obligatorio.',
            'iva.required' => 'El campo Condivión IVA es obligatorio.',
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




        $cliente = Cliente::find($id);
        try {
            $cliente->update($input);

        } catch (QueryException $ex) {

            if ($ex->errorInfo[1] == 1062) {
                $mensajeError = 'El Cliente ya existe.';
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

        return redirect()->route('clientes.index')
            ->with('success','Cliente modificado con éxito');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Cliente::find($id)->delete();

        return redirect()->route('clientes.index')
            ->with('success','Cliente eliminado con éxito');
    }

}
