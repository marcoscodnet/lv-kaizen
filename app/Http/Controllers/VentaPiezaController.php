<?php

namespace App\Http\Controllers;

use App\Models\StockPieza;
use App\Models\Sucursal;
use App\Models\VentaPieza;
use App\Models\PiezaVentaPieza;
use App\Http\Controllers\Controller;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use PDF;
class VentaPiezaController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:venta-pieza-listar|venta-pieza-crear|venta-pieza-editar|venta-pieza-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:venta-pieza-crear', ['only' => ['create','store']]);
        $this->middleware('permission:venta-pieza-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:venta-pieza-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $ventaPiezas = VentaPieza::all();
        $users = \App\Models\User::orderBy('name')
            ->pluck('name', 'id')
            ->prepend('Todos', '-1');
        return view ('ventaPiezas.index',compact('ventaPiezas','users'));
    }


    public function dataTable(Request $request)
    {
        $columnas = [  'venta_piezas.fecha','venta_piezas.cliente','venta_piezas.pedido','venta_piezas.destino',DB::raw("(
        SELECT SUM(pvp.precio)
        FROM pieza_venta_piezas pvp
        WHERE pvp.venta_pieza_id = venta_piezas.id
    ) as precio_total"),'sucursals.nombre', DB::raw("IFNULL(users.name, venta_piezas.user_name)"),
            DB::raw("(
    SELECT GROUP_CONCAT(p.codigo SEPARATOR ', ')
    FROM pieza_venta_piezas pvp
    INNER JOIN piezas p ON p.id = pvp.pieza_id
    WHERE pvp.venta_pieza_id = venta_piezas.id
) as piezas_codigos")

        ]; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');
        $user_id = $request->input('user_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $query = VentaPieza::select('venta_piezas.id as id', 'venta_piezas.fecha','venta_piezas.cliente','venta_piezas.pedido','venta_piezas.destino',DB::raw("(
            SELECT SUM(pvp.precio)
            FROM pieza_venta_piezas pvp
            WHERE pvp.venta_pieza_id = venta_piezas.id
        ) as precio_total"),'sucursals.nombre as sucursal_nombre',DB::raw("IFNULL(users.name, venta_piezas.user_name) as usuario_nombre"),
            DB::raw("(
    SELECT GROUP_CONCAT(p.codigo SEPARATOR ', ')
    FROM pieza_venta_piezas pvp
    INNER JOIN piezas p ON p.id = pvp.pieza_id
    WHERE pvp.venta_pieza_id = venta_piezas.id
) as piezas_codigos")

        )
            ->leftJoin('sucursals', 'venta_piezas.sucursal_id', '=', 'sucursals.id')

            ->leftJoin('users', 'venta_piezas.user_id', '=', 'users.id')
        ;

        if (!empty($user_id)) {

            $request->session()->put('user_filtro_venta_pieza', $user_id);

        }
        else{
            $user_id = $request->session()->get('user_filtro_venta_pieza');

        }
        if ($user_id=='-1'){
            $request->session()->forget('user_filtro_venta_pieza');
            $user_id='';
        }
        if (!empty($user_id)) {

            $query->where('venta_piezas.user_id', $user_id);


        }





        if (!empty($fechaDesde)) {
            $query->whereDate('venta_piezas.fecha', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('venta_piezas.fecha', '<=', $fechaHasta);
        }


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
        $recordsTotal = VentaPieza::count();



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


        $stockPiezas = StockPieza::with(['pieza', 'sucursal'])
            ->get()
            ->map(function ($sp) {
                return [
                    'id' => $sp->pieza_id,
                    'codigo' => $sp->pieza->codigo,
                    'descripcion' => $sp->pieza->descripcion,
                    'sucursal_id' => $sp->sucursal_id,
                    'sucursal_nombre' => $sp->sucursal->nombre,
                    'costo' => $sp->pieza->costo,
                    'precio_minimo' => $sp->pieza->precio_minimo,
                ];
            })
            ->unique(function ($item) {
                return $item['id'] . '-' . $item['sucursal_id'];
            })
            ->values();

        $stockPiezasJson = $stockPiezas->groupBy('id');


        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        return view('ventaPiezas.create', compact('users','stockPiezasJson','sucursals'));
    }

    public function store(Request $request)
    {
        //dd($request);
        $rules = [
            'user_id' => 'required',

            'fecha' => 'required|date',
            'pieza_id' => 'required|array|min:1',
            'pieza_id.*' => 'required|distinct',
        ];


        // Definir los mensajes de error personalizados
        $messages = [

            'fecha.required' => 'La fecha es obligatoria.',
            'pieza_id.required' => 'Debe agregar al menos una pieza.',
            'pieza_id.min' => 'Debe agregar al menos una pieza.',
            'pieza_id.*.required' => 'Debe seleccionar una pieza.',
            'pieza_id.*.distinct' => 'No puede repetir piezas.',
            'sucursal_id.*.required' => 'Debe seleccionar una sucursal.',
        ];

        // Agregar validaciones condicionales según el destino
        switch ($request->input('destino')) {
            case 'Salón':
                $rules['cliente'] = 'required';
                $rules['documento'] = 'required';
                $rules['telefono'] = 'required';
                $rules['moto'] = 'required';

                $messages['cliente.required'] = 'El campo Cliente es obligatorio.';
                $messages['documento.required'] = 'El campo Documento es obligatorio.';
                $messages['telefono.required'] = 'El campo Teléfono es obligatorio.';
                $messages['moto.required'] = 'El campo Moto es obligatorio.';
                break;

            case 'Sucursal':
                $rules['sucursal_id'] = 'required';
                /*$rules['pedido'] = 'required';*/

                $messages['sucursal_id.required'] = 'Debe seleccionar una sucursal.';
                /*$messages['pedido.required'] = 'Debe ingresar el Nro. de Pedido de Reparación.';*/
                break;

            case 'Taller':
                /*$rules['pedido'] = 'required';

                $messages['pedido.required'] = 'Debe ingresar el Nro. de Pedido de Reparación.';*/
                break;
        }
        // Crear el validador con las reglas y mensajes
        $validator = Validator::make($request->all(), $rules, $messages);

        // Validar y verificar si hay errores
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $input = $this->sanitizeInput($request->all());


        DB::beginTransaction();
        $ok=1;
        try {
            // Guardar la venta principal
            $venta = new VentaPieza();
            $venta->user_id = $this->sanitizeInput($request->user_id);
            $venta->fecha = $this->sanitizeInput($request->fecha);
            $venta->destino = $this->sanitizeInput($request->destino);
            $venta->cliente = $this->sanitizeInput($request->cliente);
            $venta->documento = $this->sanitizeInput($request->documento);
            $venta->telefono = $this->sanitizeInput($request->telefono);
            $venta->moto = $this->sanitizeInput($request->moto);
            $venta->sucursal_id = $this->sanitizeInput($request->sucursal_id); // solo si aplica
            $venta->pedido = $this->sanitizeInput($request->pedido); // solo si aplica
            $venta->save();

            // Guardar piezas relacionadas
            foreach ($request->pieza_id as $i => $piezaId) {
                $detalle = new PiezaVentaPieza();
                $detalle->venta_pieza_id = $venta->id;
                $detalle->pieza_id = $piezaId;
                $detalle->sucursal_id = $this->sanitizeInput($request->sucursal_id_item[$i]);
                /*$detalle->costo = $request->costo[$i];
                $detalle->precio_minimo = $request->precio_minimo[$i];*/
                $detalle->cantidad = $this->sanitizeInput($request->cantidad[$i]);
                $detalle->precio = $this->sanitizeInput($request->precio[$i]);
                $detalle->save();
                $stockPiezas = StockPieza::where('pieza_id',$piezaId)->where('sucursal_id',$request->sucursal_id_item[$i])->get();
                $cantidadRestante = $request->cantidad[$i];

                foreach ($stockPiezas as $stockPieza) {
                    if ($stockPieza->cantidad >= $cantidadRestante) {
                        // Descontar y guardar
                        $stockPieza->cantidad -= $cantidadRestante;
                        $cantidadRestante = 0;
                    } else {
                        // Descontar todo lo que tenga y continuar
                        $cantidadRestante -= $stockPieza->cantidad;
                        $stockPieza->cantidad = 0;
                    }

                    // Si después del descuento, la cantidad es 0, eliminamos el stock
                    if ($stockPieza->cantidad == 0) {
                        $stockPieza->delete(); // o $stockPieza->forceDelete() si es soft deletes
                    } else {
                        // Guardar el stock actualizado
                        $stockPieza->save();
                    }

                    if ($cantidadRestante <= 0) {
                        break; // Ya se descontó todo lo necesario
                    }
                }
            }

        }catch(QueryException $ex){
            $error = $ex->getMessage();
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

        return redirect()->route('ventaPiezas.index')->with($respuestaID,$respuestaMSJ);



    }


    public function update(Request $request, $id)
    {
        //dd($request);
        $rules = [
            'user_id' => 'required',
            'fecha' => 'required|date',
            'pieza_id' => 'required|array|min:1',
            'pieza_id.*' => 'required|distinct',
        ];

        $messages = [
            'fecha.required' => 'La fecha es obligatoria.',
            'pieza_id.required' => 'Debe agregar al menos una pieza.',
            'pieza_id.min' => 'Debe agregar al menos una pieza.',
            'pieza_id.*.required' => 'Debe seleccionar una pieza.',
            'pieza_id.*.distinct' => 'No puede repetir piezas.',
        ];

        switch ($request->input('destino')) {
            case 'Salón':
                $rules['cliente'] = 'required';
                $rules['documento'] = 'required';
                $rules['telefono'] = 'required';
                $rules['moto'] = 'required';

                $messages['cliente.required'] = 'El campo Cliente es obligatorio.';
                $messages['documento.required'] = 'El campo Documento es obligatorio.';
                $messages['telefono.required'] = 'El campo Teléfono es obligatorio.';
                $messages['moto.required'] = 'El campo Moto es obligatorio.';
                break;

            case 'Sucursal':
                $rules['sucursal_id'] = 'required';
                $messages['sucursal_id.required'] = 'Debe seleccionar una sucursal.';
                break;
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        $ok = true;

        try {
            // Anulamos la venta anterior
            $this->destroy($id);

            // Creamos una nueva venta (puede ser necesario adaptar `store()` para recibir `$request` como parámetro)
            $this->store($request);

        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al actualizar: ' . $ex->getMessage());
        }

        DB::commit();

        return redirect()->route('ventaPiezas.index')->with('success', 'Registro actualizado correctamente');
    }


    public function edit($id)
    {
        $ventaPieza = VentaPieza::with(['piezas', 'piezas.pieza', 'piezas.sucursal'])->findOrFail($id);

        $stockPiezas = StockPieza::with(['pieza', 'sucursal'])
            ->get()
            ->map(function ($sp) {
                return [
                    'id' => $sp->pieza_id,
                    'codigo' => $sp->pieza->codigo,
                    'descripcion' => $sp->pieza->descripcion,
                    'sucursal_id' => $sp->sucursal_id,
                    'sucursal_nombre' => $sp->sucursal->nombre,
                    'costo' => $sp->pieza->costo,
                    'precio_minimo' => $sp->pieza->precio_minimo,
                ];
            })
            ->unique(function ($item) {
                return $item['id'] . '-' . $item['sucursal_id'];
            })
            ->values();

        $stockPiezasJson = $stockPiezas->groupBy('id');

        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('ventaPiezas.edit', compact('ventaPieza', 'users', 'stockPiezasJson', 'sucursals'));
    }


    public function show($id)
    {
        $ventaPieza = VentaPieza::with(['piezas', 'piezas.pieza', 'piezas.sucursal'])->findOrFail($id);

        $stockPiezas = StockPieza::with(['pieza', 'sucursal'])
            ->get()
            ->map(function ($sp) {
                return [
                    'id' => $sp->pieza_id,
                    'codigo' => $sp->pieza->codigo,
                    'descripcion' => $sp->pieza->descripcion,
                    'sucursal_id' => $sp->sucursal_id,
                    'sucursal_nombre' => $sp->sucursal->nombre,
                    'costo' => $sp->pieza->costo,
                    'precio_minimo' => $sp->pieza->precio_minimo,
                ];
            })
            ->unique(function ($item) {
                return $item['id'] . '-' . $item['sucursal_id'];
            })
            ->values();

        $stockPiezasJson = $stockPiezas->groupBy('id');

        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('ventaPiezas.show', compact('ventaPieza', 'users', 'stockPiezasJson', 'sucursals'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        DB::transaction(function () use ($id) {
            $venta = VentaPieza::with('piezas.pieza', 'piezas.sucursal')->findOrFail($id);

            foreach ($venta->piezas as $pvp) {
                if ($pvp->cantidad > 0) {
                    // Sumar stock existente o crear uno nuevo
                    $stock = StockPieza::where('pieza_id', $pvp->pieza_id)
                        ->where('sucursal_id', $pvp->sucursal_id)
                        ->first();

                    if ($stock) {
                        $stock->cantidad += $pvp->cantidad;
                        $stock->save();
                    } else {
                        StockPieza::create([
                            'pieza_id' => $pvp->pieza_id,
                            'sucursal_id' => $pvp->sucursal_id,
                            'cantidad' => $pvp->cantidad,
                            'remito' => 'venta anulada',
                            'ingreso' => Carbon::now()->toDateString(),
                            'costo' => $pvp->pieza->costo ?? 0,
                            'precio_minimo' => $pvp->pieza->precio_minimo ?? 0,
                            'proveedor' => null,
                        ]);
                    }
                }
            }

            // Eliminar relaciones
            PiezaVentaPieza::where('venta_pieza_id', $venta->id)->delete();

            // Eliminar la venta
            $venta->delete();
        });

        return redirect()->route('ventaPiezas.index')
            ->with('success','Venta pieza anulada con éxito');
    }

    public function generatePDF(Request $request,$attach = false)
    {
        $ventaPiezaId = $request->query('venta_pieza_id');
        $ventaPieza = VentaPieza::find($ventaPiezaId);



        $template = 'ventaPiezas.pdf';
        /*$unidadMovimientos = $ventaPieza->unidadMovimientos()->get();*/
        $destino='';
        $descripcion='';
        switch ($ventaPieza->destino) {
            case 'Salón':
                $destino ='Apellido y Nombre: '.$ventaPieza->cliente.'<br>Moto: '.$ventaPieza->moto.'<br>Documento: '.$ventaPieza->documento.'<br>Tel: '.$ventaPieza->telefono;
                $descripcion='Descripción:<br>'.$ventaPieza->descripcion;
                break;

            case 'Sucursal':
                $destino ='Sucursal: '.$ventaPieza->sucursal->nombre;
                $descripcion='Nro. de Reparación: '.$ventaPieza->pedido;
                break;

            case 'Taller':
                $destino ='Destino: Taller';
                $descripcion='Nro. de Reparación: '.$ventaPieza->pedido;
                break;
        }


        $data = [
            'remito' => str_pad($ventaPieza->id,8,'0',STR_PAD_LEFT),
            'fecha' => $ventaPieza->fecha,
            'destino' => $destino,
            'vendedor' => (isset($ventaPieza->user))?$ventaPieza->user->name:$ventaPieza->user_name,
            'piezaVentapiezas' => $ventaPieza->piezas,
            'descripcion' => $descripcion,
        ];
        //dd($data);




        $pdf = PDF::loadView($template, $data);

        $pdfPath = 'Venta_Pieza_' . $ventaPiezaId . '.pdf';

        if ($attach) {
            $fullPath = public_path('/temp/' . $pdfPath);
            $pdf->save($fullPath);
            return $fullPath; // Devuelve la ruta del archivo para su uso posterior
        } else {

            return $pdf->download($pdfPath);
        }

        // Renderiza la vista de previsualización para HTML
        //return view('integrantes.alta', $data);
    }
}
