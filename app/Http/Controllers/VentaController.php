<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Autorizacion;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Provincia;
use App\Models\Sucursal;
use App\Models\Unidad;
use App\Models\Venta;
use App\Models\Entidad;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use PDF;
class VentaController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:venta-listar|venta-crear|venta-editar|venta-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:venta-crear', ['only' => ['create','store']]);
        $this->middleware('permission:venta-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:venta-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $ventas = Venta::all();
        return view ('ventas.index',compact('ventas'));
    }


    public function dataTable(Request $request)
    {
        $columnas = [
            'ventas.fecha',
            'clientes.nombre as cliente',
            'unidads.motor',
            'modelos.nombre as modelo',
            DB::raw("IFNULL(users.name, ventas.user_name)"),
            'sucursals.nombre as sucursal_nombre',
            DB::raw("CASE WHEN autorizacions.id IS NOT NULL THEN 'Autorizada' ELSE 'No autorizada' END as autorizacion"),
            'ventas.forma'
        ];

        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        // Query base
        $query = Venta::select(
            'ventas.id as id',
            'ventas.fecha',
            'clientes.nombre as cliente',
            'unidads.motor',
            'modelos.nombre as modelo',
            DB::raw("IFNULL(users.name, ventas.user_name) as usuario_nombre"),
            'sucursals.nombre as sucursal_nombre',
            DB::raw("CASE WHEN autorizacions.id IS NOT NULL THEN 'Autorizada' ELSE 'No autorizada' END as autorizacion"),
            'ventas.forma'
        )
            ->leftJoin('sucursals', 'ventas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->leftJoin('unidads', 'ventas.unidad_id', '=', 'unidads.id')
            ->leftJoin('productos', 'unidads.producto_id', '=', 'productos.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('users', 'ventas.user_id', '=', 'users.id')
            ->leftJoin('autorizacions', 'autorizacions.unidad_id', '=', 'unidads.id');

        // Aplicar búsqueda
        if (!empty($busqueda)) {
            $query->where(function ($query) use ($columnas, $busqueda) {
                foreach ($columnas as $columna) {
                    if ($columna) {
                        $query->orWhere($columna, 'like', "%$busqueda%");
                    }
                }
            });
        }

        // Clonar para evitar pisar el query
        $baseQuery = clone $query;

        // Totales
        $totalVentas = (clone $baseQuery)->count();
        $ventasAutorizadas = (clone $baseQuery)->whereNotNull('autorizacions.id')->count();
        $ventasNoAutorizadas = $totalVentas - $ventasAutorizadas;

        // IDs de ventas para pagos
        $ventaIds = (clone $baseQuery)->pluck('ventas.id');
        $totalAcreditado = Pago::whereIn('venta_id', $ventaIds)->sum('pagado');
        $totalVentasImporte = Pago::whereIn('venta_id', $ventaIds)->sum('monto');

        // Cantidad filtrada
        $recordsFiltered = (clone $baseQuery)->count();

        // Datos paginados
        $datos = (clone $baseQuery)
            ->orderBy($columnaOrden, $orden)
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Total sin filtros
        $recordsTotal = Venta::count();

        return response()->json([
            'data' => $datos,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
            'totales' => [
                'totalVentas' => $totalVentas,
                'ventasAutorizadas' => $ventasAutorizadas,
                'ventasNoAutorizadas' => $ventasNoAutorizadas,
                'totalAcreditado' => $totalAcreditado,
                'totalVentasImporte' => $totalVentasImporte
            ]
        ]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function unidads(Request $request)
    {

        $unidads = Unidad::all();
        return view ('ventas.unidads',compact('unidads'));
    }


    public function unidadDataTable(Request $request)
    {
        $columnas = ['tipo_unidads.nombre','marcas.nombre','modelos.nombre','colors.nombre','sucursals.nombre','unidads.ingreso','unidads.year','unidads.envio','unidads.motor','unidads.cuadro']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');

        $query = Unidad::select('unidads.id as id', 'tipo_unidads.nombre as tipo_unidad_nombre', 'marcas.nombre as marca_nombre', 'modelos.nombre as modelo_nombre', 'colors.nombre as color_nombre','sucursals.nombre as sucursal_nombre','unidads.ingreso','unidads.year','unidads.envio','unidads.motor','unidads.cuadro')
            ->leftJoin('productos', 'unidads.producto_id', '=', 'productos.id')
            ->leftJoin('sucursals', 'unidads.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('tipo_unidads', 'productos.tipo_unidad_id', '=', 'tipo_unidads.id')
            ->leftJoin('marcas', 'productos.marca_id', '=', 'marcas.id')
            ->leftJoin('modelos', 'productos.modelo_id', '=', 'modelos.id')
            ->leftJoin('colors', 'productos.color_id', '=', 'colors.id')
            ->where('productos.discontinuo',0)
            ->whereNotIn('unidads.id', function ($q) {
                $q->select('unidad_id')->from('ventas');
            });


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
        $recordsTotal = Unidad::count();



        return response()->json([
            'data' => $datos, // Obtener solo los elementos paginados
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $request->draw,
        ]);
    }

    public function vender($id)
    {
        $unidad = Unidad::find($id);
        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $entidads = Entidad::orderBy('nombre')->where('activa',1)->pluck('nombre', 'id')->prepend('', '');
        return view('ventas.vender', compact('users','sucursals', 'unidad','provincias','entidads'));
    }

    public function store(Request $request)
    {
        //dd($request->all());

        $precioSugerido = $request->input('precio', 0);

// Sumamos los montos ingresados
        $totalMonto = $request->input('totalMonto', 0);
        $totalAcreditado = $request->input('totalAcreditado', 0);


        $rules = [
            'unidad_id' => 'required',
            'user_id' => 'required',
            'cliente_id' => 'required',
            'sucursal_id' => 'required',
            'forma' => 'required',
            'fecha' => 'required|date',
            'entidad_id' => 'required|array|min:1',
            'entidad_id.*' => 'required',
            'monto.*' => 'required|numeric|min:1',
            'fecha_pago.*' => 'required|date',
            'pagado.*' => 'nullable|numeric|min:0',
            'contadora.*' => 'nullable|date',
        ];


        // Definir los mensajes de error personalizados
        $messages = [

            'fecha.required' => 'La fecha es obligatoria.',
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'entidad_id.required' => 'Debe agregar al menos un pago.',
            'entidad_id.min' => 'Debe agregar al menos un pago.',
            'entidad_id.*.required' => 'Debe seleccionar una entidad.',
            'monto.*.required' => 'El importe es obligatorio.',
            'fecha_pago.*.required' => 'La fecha de pago es obligatoria.',
        ];



        // Crear el validador con las reglas y mensajes
        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($validator) use ($totalMonto, $precioSugerido) {
            if ($totalMonto < $precioSugerido) {
                $validator->errors()->add('monto', "El importe total de los pagos ($totalMonto) debe ser igual o mayor al precio sugerido ($precioSugerido).");
            }
        });

        // Validar y verificar si hay errores
        if ($validator->fails()) {
            $cliente = Cliente::find($request->input('cliente_id'));
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->all() + [
                        'cliente_nombre' => optional($cliente)->full_name_phone, // 👈 tu accessor
                    ]);
        }


        $input = $this->sanitizeInput($request->all());


        DB::beginTransaction();
        $ok=1;
        try {
            // Guardar la venta principal
            $venta = new Venta();
            $venta->unidad_id = $this->sanitizeInput($request->unidad_id);
            $venta->user_id = $this->sanitizeInput($request->user_id);
            $venta->cliente_id = $this->sanitizeInput($request->cliente_id);
            $venta->sucursal_id = $this->sanitizeInput($request->sucursal_id);
            $venta->fecha = $this->sanitizeInput($request->fecha);
            $venta->monto = $this->sanitizeInput($request->precio);
            $venta->total = $this->sanitizeInput($request->precio);
            $venta->forma = $this->sanitizeInput($request->forma);

            $venta->save();

            // Guardar piezas relacionadas
            foreach ($request->entidad_id as $i => $entidadId) {
                $detalle = new Pago();
                $detalle->venta_id = $venta->id;
                $detalle->entidad_id = $entidadId;
                $detalle->monto = $this->sanitizeInput($request->monto[$i]);
                $detalle->fecha = $this->sanitizeInput($request->fecha_pago[$i]);
                $detalle->pagado = $this->sanitizeInput($request->pagado[$i]);
                $detalle->contadora = $this->sanitizeInput($request->contadora[$i]);
                $detalle->detalle = $this->sanitizeInput($request->detalle[$i]);
                $detalle->observacion = $this->sanitizeInput($request->observaciones[$i]);
                $detalle->save();

            }
            $autorizada = $this->sanitizeInput($request->autorizada);
            if ($autorizada){
                $autorizacion = new Autorizacion();
                $autorizacion->user_id = $this->sanitizeInput($request->user_id);
                $autorizacion->unidad_id = $this->sanitizeInput($request->unidad_id);
                $autorizacion->fecha = $this->sanitizeInput($request->fecha);
                $autorizacion->save();
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

        return redirect()->route('ventas.index')->with($respuestaID,$respuestaMSJ);



    }

    public function edit($id) {
        $venta = Venta::with('pagos', 'unidad', 'cliente')->findOrFail($id);
        $users = \App\Models\User::where('activo', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $entidads = Entidad::orderBy('nombre')->where('activa',1)->pluck('nombre', 'id')->prepend('', '');

        return view('ventas.edit', compact('venta', 'users', 'sucursals', 'entidads','provincias'));
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::with('pagos')->findOrFail($id);

        $precioSugerido = $request->input('precio', 0);
        $totalMonto = $request->input('totalMonto', 0);
        $totalAcreditado = $request->input('totalAcreditado', 0);

        $rules = [
            'unidad_id' => 'required',
            'user_id' => 'required',
            'cliente_id' => 'required',
            'sucursal_id' => 'required',
            'forma' => 'required',
            'fecha' => 'required|date',
            'entidad_id' => 'required|array|min:1',
            'entidad_id.*' => 'required',
            'monto.*' => 'required|numeric|min:1',
            'fecha_pago.*' => 'required|date',
            'pagado.*' => 'nullable|numeric|min:0',
            'contadora.*' => 'nullable|date',
        ];

        $messages = [
            'fecha.required' => 'La fecha es obligatoria.',
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'entidad_id.required' => 'Debe agregar al menos un pago.',
            'entidad_id.min' => 'Debe agregar al menos un pago.',
            'entidad_id.*.required' => 'Debe seleccionar una entidad.',
            'monto.*.required' => 'El importe es obligatorio.',
            'fecha_pago.*.required' => 'La fecha de pago es obligatoria.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($validator) use ($totalMonto, $precioSugerido) {
            if ($totalMonto < $precioSugerido) {
                $validator->errors()->add('monto', "El importe total de los pagos ($totalMonto) debe ser igual o mayor al precio sugerido ($precioSugerido).");
            }
        });

        if ($validator->fails()) {
            $cliente = Cliente::find($request->input('cliente_id'));
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->all() + [
                        'cliente_nombre' => optional($cliente)->full_name_phone,
                    ]);
        }

        $input = $this->sanitizeInput($request->all());

        DB::beginTransaction();
        $ok = 1;
        try {
            // Actualizar la venta
            $venta->unidad_id = $input['unidad_id'];
            $venta->user_id = $input['user_id'];
            $venta->cliente_id = $input['cliente_id'];
            $venta->sucursal_id = $input['sucursal_id'];
            $venta->fecha = $input['fecha'];
            $venta->monto = $input['precio'];
            $venta->total = $input['precio'];
            $venta->forma = $input['forma'];
            $venta->save();

            // Eliminar pagos anteriores
            $venta->pagos()->delete();

            // Guardar pagos nuevos
            foreach ($request->entidad_id as $i => $entidadId) {
                $detalle = new Pago();
                $detalle->venta_id = $venta->id;
                $detalle->entidad_id = $entidadId;
                $detalle->monto = $input['monto'][$i];
                $detalle->fecha = $input['fecha_pago'][$i];
                $detalle->pagado = $input['pagado'][$i] ?? null;
                $detalle->contadora = $input['contadora'][$i] ?? null;
                $detalle->detalle = $input['detalle'][$i] ?? null;
                $detalle->observacion = $input['observaciones'][$i] ?? null;
                $detalle->save();
            }



        } catch(QueryException $ex) {
            $error = $ex->getMessage();
            $ok = 0;
        }

        if ($ok) {
            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'Registro actualizado satisfactoriamente');
        } else {
            DB::rollback();
            return redirect()->back()->with('error', $error);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);

        // Buscar la autorización correspondiente a esa unidad
        $autorizacion = Autorizacion::where('unidad_id', $venta->unidad_id)
            ->first();

        if ($autorizacion) {
            $autorizacion->delete();
        }

        // Elimina las relaciones
        $venta->pagos()->delete();


        // Elimina el venta
        $venta->delete();

        return redirect()->route('ventas.index')
            ->with('success','Venta eliminada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function autorizar($id)
    {

        $venta = Venta::findOrFail($id);


        $autorizacion = new Autorizacion();
        $autorizacion->user_id = $this->sanitizeInput(auth()->id());
        $autorizacion->unidad_id = $this->sanitizeInput($venta->unidad_id);
        $autorizacion->fecha = $this->sanitizeInput(Carbon::now()->toDateString());
        $autorizacion->save();

        return redirect()->route('ventas.index')
            ->with('success','Unidad autorizada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function desautorizar($id)
    {

        $venta = Venta::findOrFail($id);

        // Buscar la autorización correspondiente a esa unidad
        $autorizacion = Autorizacion::where('unidad_id', $venta->unidad_id)

            ->first();

        if ($autorizacion) {
            $autorizacion->delete();
        }


        return redirect()->route('ventas.index')
            ->with('success','Unidad desautorizada con éxito');
    }

    public function generateBoleto(Request $request,$attach = false)
    {
        $ventaId = $request->query('venta_id');
        $venta = Venta::find($ventaId);



        $template = 'ventas.boleto';
        /*$unidadMovimientos = $ventaPieza->unidadMovimientos()->get();*/


        $data = [
            //'remito' => str_pad($ventaPieza->id,8,'0',STR_PAD_LEFT),
            'venta' => $venta,
            'fecha' => $venta->fecha,
            //'destino' => $destino,
            'vendedor' => (isset($venta->user))?$venta->user->name:$venta->user_name,
            /*'piezaVentapiezas' => $ventaPieza->piezas,
            'descripcion' => $descripcion,*/
        ];
        //dd($data);




        $pdf = PDF::loadView($template, $data);

        $pdfPath = 'Venta_' . $ventaId . '.pdf';

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
