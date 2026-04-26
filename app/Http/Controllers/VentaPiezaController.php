<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Concepto;
use App\Models\Entidad;
use App\Models\MovimientoCaja;
use App\Models\Provincia;
use App\Models\StockPieza;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\VentaPieza;
use App\Models\PiezaVentaPieza;
use App\Models\Pago;
use App\Http\Controllers\Controller;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $columnas = [  'venta_piezas.fecha',DB::raw("IFNULL(clientes.nombre, venta_piezas.cliente)"),'venta_piezas.pedido','venta_piezas.destino',DB::raw("(
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
        $query = VentaPieza::select('venta_piezas.id as id', 'venta_piezas.fecha',DB::raw("IFNULL(clientes.nombre, venta_piezas.cliente) as cliente"),'venta_piezas.pedido','venta_piezas.destino',DB::raw("(
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
            ->leftJoin('clientes', 'venta_piezas.cliente_id', '=', 'clientes.id')
        ;




        if (!empty($user_id) && $user_id != '-1') {
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

        $user = auth()->user();
        $esAdministrador = $user->hasRole('Administrador');

        $stockPiezasQuery = StockPieza::with(['pieza', 'sucursal'])
            ->where('cantidad', '>', 0); // Only show pieces with available stock

        // Non-admin users see only stock from their own branch
        if (!$esAdministrador) {
            $stockPiezasQuery->where('sucursal_id', $user->sucursal_id);
        }
        $stockPiezas = $stockPiezasQuery
            ->get()
            ->map(function ($sp) {
                return [
                    'id'              => $sp->pieza_id,
                    'codigo'          => $sp->pieza->codigo,
                    'descripcion'     => $sp->pieza->descripcion,
                    'sucursal_id'     => $sp->sucursal_id,
                    'sucursal_nombre' => $sp->sucursal->nombre,
                    'costo'           => $sp->pieza->costo,
                    'precio_minimo'   => $sp->pieza->precio_minimo,
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
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        // Load open service orders for the Taller destination dropdown
        $serviciosAbiertos = \App\Models\Servicio::where('pagado', 0)
            ->orderBy('id', 'desc')
            ->get(['id', 'modelo', 'motor', 'chasis']);

        $entidads = \App\Models\Entidad::orderBy('nombre')->where('activa', 1)->get(['id', 'nombre', 'forma']);
        return view('ventaPiezas.create', compact('users', 'stockPiezasJson', 'sucursals', 'provincias', 'serviciosAbiertos', 'entidads'));
    }

    private function guardarVenta(Request $request): VentaPieza
    {
        $input = $this->sanitizeInput($request->all());

        // Validate stock before saving
        foreach ($request->pieza_id as $i => $piezaId) {
            $sucursalId = $request->sucursal_id_item[$i];
            $cantidadSolicitada = $request->cantidad[$i];

            $stockDisponible = StockPieza::where('pieza_id', $piezaId)
                ->where('sucursal_id', $sucursalId)
                ->sum('cantidad');

            if ($stockDisponible < $cantidadSolicitada) {
                throw new \Exception("No hay suficiente stock de la pieza {$piezaId} en la sucursal seleccionada.");
            }
        }

        // Save main sale
        $venta = new VentaPieza();
        $venta->user_id    = $input['user_id'];
        $venta->fecha      = $input['fecha'];
        $venta->destino    = $input['destino'];
        $venta->cliente_id = $input['cliente_id'] ?? null;
        $venta->sucursal_id = $input['sucursal_id'] ?? null;
        $venta->pedido     = $input['pedido'] ?? null;
        $venta->servicio_id = ($input['destino'] === 'Taller') ? ($input['servicio_id'] ?? null) : null;
        $venta->forma      = ($input['destino'] === 'Salón') ? ($input['forma'] ?? null) : null;
        $venta->save();

        // Save details and discount stock
        foreach ($request->pieza_id as $i => $piezaId) {
            $detalle = new PiezaVentaPieza();
            $detalle->venta_pieza_id = $venta->id;
            $detalle->pieza_id       = $piezaId;
            $detalle->sucursal_id    = $request->sucursal_id_item[$i];
            $detalle->cantidad       = $request->cantidad[$i];
            $detalle->precio         = $request->precio[$i];
            $detalle->save();

            $stockPiezas = StockPieza::where('pieza_id', $piezaId)
                ->where('sucursal_id', $request->sucursal_id_item[$i])
                ->orderBy('id')
                ->get();

            $cantidadRestante = $request->cantidad[$i];

            foreach ($stockPiezas as $stockPieza) {
                if ($stockPieza->cantidad >= $cantidadRestante) {
                    $stockPieza->cantidad -= $cantidadRestante;
                    $cantidadRestante = 0;
                } else {
                    $cantidadRestante -= $stockPieza->cantidad;
                    $stockPieza->cantidad = 0;
                }
                $stockPieza->save();
                if ($cantidadRestante <= 0) {
                    break;
                }
            }
        }

        // Save payments only for Salón
        if ($input['destino'] === 'Salón' && $request->filled('entidad_id')) {
            // Check for open cash register
            $cajaAbierta = Caja::where('sucursal_id', $request->sucursal_id)
                ->where('user_id', $request->user_id)
                ->where('estado', 'Abierta')
                ->first();

            if (!$cajaAbierta) {
                throw new \Exception("No hay caja abierta para esta sucursal y usuario. No se puede registrar el pago.");
            }

            $conceptoVenta = Concepto::firstOrCreate(['nombre' => 'Venta de pieza']);

            foreach ($request->entidad_id as $i => $entidadId) {
                $pago = new Pago();
                $pago->venta_pieza_id = $venta->id;
                $pago->entidad_id     = $entidadId;
                $pago->monto          = $this->sanitizeInput($request->monto[$i]);
                $pago->fecha          = $this->sanitizeInput($request->fecha_pago[$i]);
                $pago->pagado         = $this->sanitizeInput($request->pagado[$i]);
                $pago->contadora      = $this->sanitizeInput($request->contadora[$i]);
                $pago->detalle        = $this->sanitizeInput($request->detalle[$i]);
                $pago->observacion    = $this->sanitizeInput($request->observaciones[$i]);
                $pago->save();

                $entidad = Entidad::find($entidadId);
                if ($entidad) {

                    if ($entidad->tangible) {
                        // Cash payment: impacts physical cash register
                        MovimientoCaja::create([
                            'caja_id'        => $cajaAbierta->id,
                            'concepto_id'    => $conceptoVenta->id,
                            'entidad_id'     => $entidad->id,
                            'venta_pieza_id' => $venta->id,
                            'tipo'           => 'Ingreso',
                            'monto'       => $pago->pagado ,
                            'acreditado'     => 1,
                            'fecha'          => now(),
                            'user_id'        => $request->user_id,
                            'referencia'     => $pago->detalle,
                        ]);
                    }
                    if ($entidad->cuenta) {
                        // Non-tangible payment: impacts entity account only
                        \App\Models\MovimientoCuenta::create([
                            'entidad_id' => $entidad->id,
                            'tipo'       => 'Ingreso',
                            'monto'      => $pago->monto,
                            'fecha'      => $pago->fecha,
                            'concepto'   => $conceptoVenta->nombre,
                            'venta_pieza_id'   => $venta->id,
                            'pago_id'    => $pago->id,
                            'user_id'    => $request->user_id,
                        ]);
                    }
                }
            }
        }

        return $venta;
    }

    public function store(Request $request)
    {
        $rules = [
            'user_id'      => 'required',
            'fecha'        => 'required|date',
            'pieza_id'     => 'required|array|min:1',
            'pieza_id.*'   => 'required|distinct',
        ];

        $messages = [
            'fecha.required'        => 'La fecha es obligatoria.',
            'pieza_id.required'     => 'Debe agregar al menos una pieza.',
            'pieza_id.min'          => 'Debe agregar al menos una pieza.',
            'pieza_id.*.required'   => 'Debe seleccionar una pieza.',
            'pieza_id.*.distinct'   => 'No puede repetir piezas.',
        ];

        switch ($request->input('destino')) {
            case 'Salón':
                $rules['cliente_id'] = 'required';
                $messages['cliente_id.required'] = 'El campo Cliente es obligatorio.';
                break;
            case 'Sucursal':
                $rules['sucursal_id'] = 'required';
                $messages['sucursal_id.required'] = 'Debe seleccionar una sucursal.';
                break;
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $this->guardarVenta($request);
            DB::commit();
            return redirect()->route('ventaPiezas.index')->with('success', 'Registro creado satisfactoriamente');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => $ex->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'user_id'      => 'required',
            'fecha'        => 'required|date',
            'pieza_id'     => 'required|array|min:1',
            'pieza_id.*'   => 'required|distinct',
        ];

        $messages = [
            'fecha.required'        => 'La fecha es obligatoria.',
            'pieza_id.required'     => 'Debe agregar al menos una pieza.',
            'pieza_id.min'          => 'Debe agregar al menos una pieza.',
            'pieza_id.*.required'   => 'Debe seleccionar una pieza.',
            'pieza_id.*.distinct'   => 'No puede repetir piezas.',
        ];

        switch ($request->input('destino')) {
            case 'Salón':
                $rules['cliente_id'] = 'required';
                $messages['cliente_id.required'] = 'El campo Cliente es obligatorio.';
                break;
            case 'Sucursal':
                $rules['sucursal_id'] = 'required';
                $messages['sucursal_id.required'] = 'Debe seleccionar una sucursal.';
                break;
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // destroy() has its own DB::transaction — call delete logic directly
            $venta = VentaPieza::with('piezas.pieza', 'piezas.sucursal')->findOrFail($id);

            foreach ($venta->piezas as $pvp) {
                if ($pvp->cantidad > 0) {
                    $stock = StockPieza::where('pieza_id', $pvp->pieza_id)
                        ->where('sucursal_id', $pvp->sucursal_id)
                        ->first();

                    if ($stock) {
                        $stock->cantidad += $pvp->cantidad;
                        $stock->save();
                    } else {
                        StockPieza::create([
                            'pieza_id'       => $pvp->pieza_id,
                            'sucursal_id'    => $pvp->sucursal_id,
                            'cantidad'       => $pvp->cantidad,
                            'remito'         => 'venta anulada',
                            'ingreso'        => Carbon::now()->toDateString(),
                            'costo'          => $pvp->pieza->costo ?? 0,
                            'precio_minimo'  => $pvp->pieza->precio_minimo ?? 0,
                            'proveedor'      => null,
                        ]);
                    }
                }
            }

            PiezaVentaPieza::where('venta_pieza_id', $venta->id)->delete();
            Pago::where('venta_pieza_id', $venta->id)->delete();
            $venta->delete();

            $this->guardarVenta($request);

            DB::commit();
            return redirect()->route('ventaPiezas.index')->with('success', 'Registro actualizado correctamente');

        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al actualizar: ' . $ex->getMessage());
        }
    }


    public function edit($id)
    {
        $ventaPieza = VentaPieza::with(['piezas', 'piezas.pieza', 'piezas.sucursal'])->findOrFail($id);

        $user = auth()->user();
        $esAdministrador = $user->hasRole('Administrador');

        $stockPiezasQuery = StockPieza::with(['pieza', 'sucursal']);

        // Non-admin users see only stock from their own branch
        if (!$esAdministrador) {
            $stockPiezasQuery->where('sucursal_id', $user->sucursal_id);
        }
        $stockPiezas = $stockPiezasQuery
            ->get()
            ->map(function ($sp) {
                return [
                    'id'              => $sp->pieza_id,
                    'codigo'          => $sp->pieza->codigo,
                    'descripcion'     => $sp->pieza->descripcion,
                    'sucursal_id'     => $sp->sucursal_id,
                    'sucursal_nombre' => $sp->sucursal->nombre,
                    'costo'           => $sp->pieza->costo,
                    'precio_minimo'   => $sp->pieza->precio_minimo,
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
        $provincias = Provincia::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $entidads = \App\Models\Entidad::orderBy('nombre')->where('activa', 1)->get(['id', 'nombre', 'forma']);
        return view('ventaPiezas.edit', compact('ventaPieza', 'users', 'stockPiezasJson', 'sucursals', 'provincias', 'entidads'));
    }


    public function show($id)
    {
        $ventaPieza = VentaPieza::with(['piezas', 'piezas.pieza', 'piezas.sucursal'])->findOrFail($id);

        $user = auth()->user();
        $esAdministrador = $user->hasRole('Administrador');

        $stockPiezasQuery = StockPieza::with(['pieza', 'sucursal']);

        // Non-admin users see only stock from their own branch
        if (!$esAdministrador) {
            $stockPiezasQuery->where('sucursal_id', $user->sucursal_id);
        }
        $stockPiezas = $stockPiezasQuery
            ->get()
            ->map(function ($sp) {
                return [
                    'id'              => $sp->pieza_id,
                    'codigo'          => $sp->pieza->codigo,
                    'descripcion'     => $sp->pieza->descripcion,
                    'sucursal_id'     => $sp->sucursal_id,
                    'sucursal_nombre' => $sp->sucursal->nombre,
                    'costo'           => $sp->pieza->costo,
                    'precio_minimo'   => $sp->pieza->precio_minimo,
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
            \App\Models\Pago::where('venta_pieza_id', $venta->id)->delete();
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

    public function exportarXLS(Request $request)
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

        $busqueda = $request->search;
        $user_id = $request->user_id;
        $fechaDesde = $request->desde;
        $fechaHasta = $request->hasta;


        // ------------------------------
        // OBTENER NOMBRES DE LOS FILTROS
        // ------------------------------
        $userNombre = ($user_id && $user_id != -1)
            ? (User::find($user_id)->nombre ?? '—')
            : 'Todos';



        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
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

        if (!empty($user_id) && $user_id != '-1') {
            $query->where('venta_piezas.user_id', $user_id);
        }


        if (!empty($fechaDesde)) {
            $query->whereDate('venta_piezas.fecha', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('venta_piezas.fecha', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $piezas = $query->get();

        // ===============================
        //     📄 CREAR ARCHIVO XLSX
        // ===============================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Venta Piezas");

        // ------------------------------
        // FILTROS
        // ------------------------------
        $sheet->setCellValue('A1', 'Vendedor:');
        $sheet->setCellValue('B1', $userNombre);

        $sheet->setCellValue('A2', 'Desde:');
        $sheet->setCellValue('B2', $fechaDesde
            ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y')
            : '—');

        $sheet->setCellValue('A3', 'Hasta:');
        $sheet->setCellValue('B3', $fechaHasta
            ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y')
            : '—');


        $sheet->setCellValue('A4', 'Búsqueda:');
        $sheet->setCellValue('B4', $busqueda ?: '—');

        // Espacio antes de la tabla
        $startRow = 5;

        // ------------------------------
        // ENCABEZADOS DE LA TABLA
        // ------------------------------
        $headers = [
            "Fecha", "Cliente", "Pedido", "Destino",
            "Monto", "Sucursal", "Vendedor", "Piezas"
        ];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $startRow, $header);
            $sheet->getStyleByColumnAndRow($col, $startRow)->getFont()->setBold(true);
            $col++;
        }

        // ------------------------------
        // DATOS
        // ------------------------------
        $row = $startRow + 1;

        foreach ($piezas as $p) {
            // 🟢 Formato de fecha dd/mm/YYYY
            $sheet->setCellValue("A{$row}",
                $p->fecha
                    ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y')
                    : '—'
            );

            $sheet->setCellValue("B{$row}", $p->cliente);
            $sheet->setCellValue("C{$row}", $p->pedido);
            $sheet->setCellValue("D{$row}", $p->destino);
            $sheet->setCellValue("E{$row}", $p->precio_total);
            $sheet->setCellValue("F{$row}", $p->sucursal_nombre);
            $sheet->setCellValue("G{$row}", $p->usuario_nombre);
            $sheet->setCellValue("H{$row}", $p->piezas_codigos);


            $row++;
        }

        // AutoSize de columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------------------
        // EXPORTAR
        // ------------------------------
        $fileName = "venta_piezas.xlsx";
        $filePath = tempnam(sys_get_temp_dir(), $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }






    public function exportarPDF(Request $request)
    {
        ini_set('memory_limit', '-1'); // ilimitado
        ini_set('max_execution_time', 0);

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

        $busqueda = $request->search;
        $user_id = $request->user_id;
        $fechaDesde = $request->desde;
        $fechaHasta = $request->hasta;


        // ------------------------------
        // OBTENER NOMBRES DE LOS FILTROS
        // ------------------------------
        $userNombre = ($user_id && $user_id != -1)
            ? (User::find($user_id)->nombre ?? '—')
            : 'Todos';



        // ------------------------------
        // MISMA QUERY QUE DATATABLE()
        // ------------------------------
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

        if (!empty($user_id) && $user_id != '-1') {
            $query->where('venta_piezas.user_id', $user_id);
        }


        if (!empty($fechaDesde)) {
            $query->whereDate('venta_piezas.fecha', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('venta_piezas.fecha', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $col) {
                    $q->orWhere($col, 'like', "%$busqueda%");
                }
            });
        }

        $piezas = $query->get();

        // Pasamos datos a la vista PDF
        $data = [
            'piezas' => $piezas,
            'busqueda' => $busqueda,
            'userNombre' => $userNombre,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ];

        $pdf = PDF::loadView('ventaPiezas.exportpdf', $data)
            ->setPaper('a4', 'landscape'); // opcional

        return $pdf->download('ventaPiezas.exportpdf');
    }
}
