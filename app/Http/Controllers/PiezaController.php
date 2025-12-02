<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\TipoPieza;
use App\Models\StockPieza;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Pieza;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class PiezaController extends Controller
{
    use SanitizesInput;
    function __construct()
    {
        $this->middleware('permission:pieza-listar|pieza-crear|pieza-editar|pieza-eliminar|pieza-modificar-descripcion', ['only' => ['index','store']]);

        $this->middleware('permission:pieza-crear', ['only' => ['create','store']]);

        // Ahora ambos permisos pueden entrar a edit/update
        $this->middleware('permission:pieza-editar|pieza-modificar-descripcion', ['only' => ['edit','update']]);

        $this->middleware('permission:pieza-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $piezas = Pieza::all();
        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1');
        return view ('piezas.index',compact('piezas','sucursals'));
    }

    public function dataTable(Request $request)
    {
        $columnas = ['piezas.codigo', 'piezas.descripcion','tipo_piezas.nombre','piezas.stock_minimo','piezas.stock_actual','sucursals.nombre','ubicacions.nombre','piezas.observaciones']; // Define las columnas disponibles
        $columnaOrden = $columnas[$request->input('order.0.column')];
        $orden = $request->input('order.0.dir');
        $busqueda = $request->input('search.value');
        $sucursal_id = $request->input('sucursal_id');
        $ubicacion_id = $request->input('ubicacion_id');
        $query = Pieza::select(
            'piezas.id',
            'piezas.codigo',
            'piezas.descripcion',
            'tipo_piezas.nombre as tipo_pieza',
            'piezas.stock_minimo',
            'piezas.stock_actual',
            DB::raw("GROUP_CONCAT(DISTINCT sucursals.nombre ORDER BY sucursals.nombre SEPARATOR ' / ') as sucursal_nombre"),
            DB::raw("GROUP_CONCAT(DISTINCT ubicacions.nombre ORDER BY ubicacions.nombre SEPARATOR ' / ') as ubicacion_nombre"),
            'piezas.observaciones'
        )
            ->leftJoin('tipo_piezas', 'piezas.tipo_pieza_id', '=', 'tipo_piezas.id')
            ->leftJoin('pieza_ubicacions', 'piezas.id', '=', 'pieza_ubicacions.pieza_id')
            ->leftJoin('ubicacions', 'pieza_ubicacions.ubicacion_id', '=', 'ubicacions.id')
            ->leftJoin('sucursals', 'ubicacions.sucursal_id', '=', 'sucursals.id')
            ->groupBy(
                'piezas.id',
                'piezas.codigo',
                'piezas.descripcion',
                'tipo_piezas.nombre',
                'piezas.stock_minimo',
                'piezas.stock_actual',
                'piezas.observaciones'
            );


        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('ubicacions.sucursal_id', $sucursal_id);
        }

        if (!empty($ubicacion_id) && $ubicacion_id != '-1') {
            $query->where('ubicacions.id', $ubicacion_id);
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
        $recordsTotal = Pieza::count();



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
        $sucursales = Sucursal::where('activa', 1)
            ->orderBy('nombre')
            ->get();
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        return view('piezas.create',compact('tipos','sucursales'));
    }

    /**
     * Store a newly created resource in storage.s
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'codigo' => 'required',
            'descripcion' => 'required',
            'tipo_pieza_id' => 'required',
        ]);

        $input = $this->sanitizeInput($request->all());

        // Si se capturó foto desde la cámara
        if ($request->filled('foto')) {
            $data = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('foto'));
            $image = base64_decode($data);

            $fileName = 'pieza_' . time() . '.png';
            $filePath = 'images/piezas/'.$fileName;

            // Guardar directamente en public
            file_put_contents(public_path($filePath), $image);

            $input['foto'] = 'piezas/'.$fileName; // se guarda la ruta en DB
        }

        // 1. Crear la pieza
        $pieza = Pieza::create($input);

        // 2. Asociar ubicación si se seleccionó
        if ($request->filled('ubicacion_id')) {
            $pieza->ubicacions()->attach($request->ubicacion_id);
        }

        return redirect()->route('piezas.index')
            ->with('success','Pieza creada con éxito');
    }


    public function ajaxStore(Request $request)
    {
        $this->validate($request, [
            'codigo' => 'required',
            'descripcion' => 'required',
            'tipo_pieza_id' => 'required',
        ]);

        $input = $this->sanitizeInput($request->all());
// Si se capturó foto desde la cámara
        if ($request->filled('foto')) {
            $data = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('foto'));
            $image = base64_decode($data);

            $fileName = 'pieza_' . time() . '.png';
            $filePath = 'images/piezas/'.$fileName;

            // Guardar directamente en public
            file_put_contents(public_path($filePath), $image);

            $input['foto'] = 'piezas/'.$fileName; // se guarda la ruta en DB
        }
        $pieza = Pieza::create($input);

        // Devolver JSON
        return response()->json([
            'id' => $pieza->id,
            'codigo' => $pieza->codigo,
            'descripcion' => $pieza->descripcion
        ]);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pieza = Pieza::find($id);
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        // Agrupar stocks por sucursal y sumar la cantidad
        $stocksPorSucursal = StockPieza::where('pieza_id', $id)
            ->selectRaw('sucursal_id, SUM(cantidad) as total_cantidad')
            ->groupBy('sucursal_id')
            ->with('sucursal') // para poder acceder al nombre de la sucursal
            ->get();
        return view('piezas.show',compact('pieza','tipos','stocksPorSucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pieza = Pieza::find($id);
        $sucursales = Sucursal::where('activa', 1)
            ->orderBy('nombre')
            ->get();
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        // 🔥 Construimos un array simple para JS
        $ubicacionesActuales = $pieza->ubicacions->map(function ($u) {
            return [
                'sucursal_id'  => $u->pivot->sucursal_id, // valor que debe aparecer en el select de sucursal
                'ubicacion_id' => $u->id,                 // valor que debe aparecer en el select de ubicacion
            ];
        });

        return view('piezas.edit', compact('pieza','tipos','sucursales','ubicacionesActuales'));
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
        $pieza = Pieza::findOrFail($id);

        // Si tiene permiso para editar todo
        if ($request->user()->can('pieza-editar')) {
            $this->validate($request, [
                'codigo' => 'required|string|max:100',
                'descripcion' => 'required|string|max:500',
                'tipo_pieza_id' => 'required|integer|exists:tipo_piezas,id',
            ]);

            $input = $this->sanitizeInput($request->all());
            // Manejar la foto si viene en base64
            if ($request->filled('foto')) {
                $data = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('foto'));
                $image = base64_decode($data);

                $fileName = 'pieza_' . time() . '.png';
                $filePath = 'images/piezas/'.$fileName;

                // Guardar directamente en public
                file_put_contents(public_path($filePath), $image);

                $input['foto'] = 'piezas/'.$fileName;
            }
            $pieza->update($input);

            // -----------------------------
            //    GUARDAR UBICACIONES
            // -----------------------------

            // ubicacion_id[] viene del formulario
            $ubicaciones = $request->input('ubicacion_id', []);

            // Eliminar vacíos ("" o null)
            $ubicaciones = array_filter($ubicaciones);

            // Solo guarda ubicacion_id
            $pieza->ubicacions()->sync($ubicaciones);
        }
        // Si solo puede modificar descripción
        elseif ($request->user()->can('pieza-modificar-descripcion')) {
            $this->validate($request, [
                'descripcion' => 'required|string|max:500',
            ]);

            $pieza->descripcion = $request->input('descripcion');
            $pieza->save();
        }
        // Si no tiene permisos, prohibir
        else {
            abort(403, 'No tienes permisos para editar esta pieza.');
        }

        return redirect()->route('piezas.index')
            ->with('success','Pieza modificada con éxito');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $pieza = Pieza::findOrFail($id);

            // Intentar borrar
            $pieza->delete();

            // Si tenía foto, borrar archivo
            if ($pieza->foto && file_exists(public_path('images/'.$pieza->foto))) {
                unlink(public_path('images/'.$pieza->foto));
            }

            return redirect()->route('piezas.index')
                ->with('success', 'Pieza eliminada con éxito');

        } catch (\Illuminate\Database\QueryException $e) {

            // Error 1451 = clave foránea impide borrar
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('piezas.index')
                    ->with('error', 'No se puede eliminar la pieza porque está asociada a ventas.');
            }

            return redirect()->route('piezas.index')
                ->with('error', 'Ocurrió un error al intentar eliminar la pieza.');
        }
    }




    public function getDatos($id)
    {
        $pieza = Pieza::find($id);

        if (!$pieza) {
            return response()->json(['error' => 'No encontrada'], 404);
        }

        return response()->json([
            'costo' => $pieza->costo,
            'precio_minimo' => $pieza->precio_minimo,
        ]);
    }

    public function createMasivo()
    {
        $tipos = TipoPieza::orderBy('nombre')->pluck('nombre', 'id');
        return view('piezas.masivo', compact('tipos'));
    }


    public function storeMasivo(Request $request)
    {
        $codigos = $request->input('codigo', []);
        $tipos = $request->input('tipo_pieza_id', []);
        $descripciones = $request->input('descripcion', []);
        $fotos = $request->input('foto', []);

        $count = count($codigos);

        $dataToInsert = [];

        for ($i = 0; $i < $count; $i++) {
            // Validación simple por fila
            if (empty($codigos[$i]) || empty($tipos[$i]) || empty($descripciones[$i])) {
                return redirect()->back()->withErrors("La fila " . ($i+1) . " tiene campos vacíos")->withInput();
            }

            $data = [
                'codigo' => $codigos[$i],
                'tipo_pieza_id' => $tipos[$i],
                'descripcion' => $descripciones[$i],
                'foto' => null
            ];

            // Procesar foto Base64 si existe
            if (!empty($fotos[$i])) {
                $image = preg_replace('#^data:image/\w+;base64,#i', '', $fotos[$i]);
                $image = base64_decode($image);

                $fileName = 'pieza_' . time() . '_' . uniqid() . '.png';
                $filePath = 'images/piezas/' . $fileName;

                file_put_contents(public_path($filePath), $image);

                $data['foto'] = 'piezas/' . $fileName;
            }

            $dataToInsert[] = $data;
        }

        // Insertar todas las piezas de una vez
        Pieza::insert($dataToInsert);

        return redirect()->route('piezas.index')
            ->with('success', 'Piezas cargadas correctamente');
    }



}
