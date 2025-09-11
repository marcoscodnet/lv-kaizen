<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Movimiento;
use App\Models\Unidad;
use App\Models\UnidadMovimiento;
use App\Traits\SanitizesInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use PDF;
class MovimientoController extends Controller
{
    use SanitizesInput;
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
        $users = \App\Models\User::orderBy('name')
            ->pluck('name', 'id')
            ->prepend('Todos', '-1');
        $movimientos = Movimiento::all();
        return view ('movimientos.index',compact('movimientos','users'));
    }


    public function dataTable(Request $request)
    {
        $busqueda = $request->input('search.value');
        $user_id = $request->input('user_id');

        // Columnas que se pueden ordenar (en el mismo orden que DataTables)
        $columnsMap = [
            'usuario_nombre',
            'origen_nombre',
            'destino_nombre',
            'fecha',
            'id',
            'cuadros',
            'motores',
            'id'
        ];

        $colIndex = $request->input('order.0.column', 0);
        $dir = $request->input('order.0.dir', 'asc');
        $sortColumn = $columnsMap[$colIndex] ?? 'id';

        // Traer movimientos con sus unidades
        $movimientosQuery = Movimiento::with(['unidadMovimientos.unidad'])
            ->leftJoin('sucursals as origen', 'movimientos.sucursal_origen_id', '=', 'origen.id')
            ->leftJoin('sucursals as destino', 'movimientos.sucursal_destino_id', '=', 'destino.id')
            ->leftJoin('users', 'movimientos.user_id', '=', 'users.id')
            ->select(
                'movimientos.id as id',
                DB::raw("IFNULL(users.name, movimientos.user_name) as usuario_nombre"),
                'origen.nombre as origen_nombre',
                'destino.nombre as destino_nombre',
                'movimientos.fecha'
            );

        // Filtrar por usuario
        // Aplicar filtro solo si hay un usuario seleccionado y no es "Todos" (-1)
        if (!empty($user_id) && $user_id != '-1') {
            $movimientosQuery->where('movimientos.user_id', $user_id);
        }

        // Traer todos los movimientos filtrados
        $movimientos = $movimientosQuery->get();

        // Mapear movimientos y concatenar cuadros/motores
        $datos = $movimientos->map(function($movimiento) {
            $cuadros = $movimiento->unidadMovimientos->pluck('unidad.cuadro')->filter()->implode(', ');
            $motores = $movimiento->unidadMovimientos->pluck('unidad.motor')->filter()->implode(', ');

            return [
                'id' => $movimiento->id,
                'usuario_nombre' => $movimiento->usuario_nombre,
                'origen_nombre' => $movimiento->origen_nombre,
                'destino_nombre' => $movimiento->destino_nombre,
                'fecha' => $movimiento->fecha,
                'cuadros' => $cuadros,
                'motores' => $motores
            ];
        });

        // Filtrar búsqueda en PHP, incluyendo cuadros y motores
        if (!empty($busqueda)) {
            $busqueda = mb_strtolower($busqueda);
            $datos = $datos->filter(function($item) use ($busqueda) {
                return str_contains(mb_strtolower($item['usuario_nombre']), $busqueda)
                    || str_contains(mb_strtolower($item['origen_nombre']), $busqueda)
                    || str_contains(mb_strtolower($item['destino_nombre']), $busqueda)
                    || str_contains(mb_strtolower($item['fecha']), $busqueda)
                    || str_contains(mb_strtolower($item['cuadros']), $busqueda)
                    || str_contains(mb_strtolower($item['motores']), $busqueda);
            });
        }

        // Ordenamiento en PHP
        $datos = $dir === 'asc'
            ? $datos->sortBy($sortColumn)
            : $datos->sortByDesc($sortColumn);

        $recordsFiltered = $datos->count();
        $recordsTotal = Movimiento::count();

        // Paginación
        $datos = $datos->slice($request->input('start'))->take($request->input('length'))->values();

        return response()->json([
            'data' => $datos,
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
        $origens = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $destinos = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

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


        $input = $this->sanitizeInput($request->all());
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

                        // Actualizar sucursal_id de la unidad
                        Unidad::where('id', $request->unidad_id[$item])
                            ->update(['sucursal_id' => $request->sucursal_destino_id]);

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

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $movimiento = Movimiento::findOrFail($id);

            // Obtenés la sucursal origen desde el movimiento
            $sucursalOrigen = $movimiento->sucursal_origen_id;

            // Revertir todas las unidades que participaron en el movimiento
            foreach ($movimiento->unidadMovimientos as $um) {
                // Revertir la unidad a la sucursal original
                Unidad::where('id', $um->unidad_id)->update([
                    'sucursal_id' => $sucursalOrigen
                ]);

                // Eliminar el registro intermedio
                $um->delete();
            }

            // Finalmente, eliminar el movimiento
            $movimiento->delete();

            DB::commit();
            return redirect()->route('movimientos.index')->with('success', 'Movimiento eliminado y unidades revertidas a su sucursal original.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar movimiento: ' . $e->getMessage());
        }
    }

    public function generatePDF(Request $request,$attach = false)
    {
        $movimientoId = $request->query('movimiento_id');
        $movimiento = Movimiento::find($movimientoId);



        $template = 'movimientos.pdf';
        $unidadMovimientos = $movimiento->unidadMovimientos()->get();



        $data = [
            'remito' => $movimientoId,
            'fecha' => $movimiento->fecha,
            'origen' => $movimiento->sucursalOrigen,
            'destino' => $movimiento->sucursalDestino,
            'unidades' => $unidadMovimientos,
        ];
        //dd($data);




        $pdf = PDF::loadView($template, $data);

        $pdfPath = 'Movimiento_' . $movimientoId . '.pdf';

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

    public function show($id)
    {
        $movimiento = Movimiento::find($id);

        $users = \App\Models\User::orderBy('name')
            ->pluck('name', 'id')
            ->prepend('', '');

        $origens = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');
        $destinos = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('movimientos.show', compact('movimiento','origens','destinos','users'));
    }

}
