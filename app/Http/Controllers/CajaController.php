<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Concepto;
use App\Models\Medio;
use App\Models\Venta;
use DB;
class CajaController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:caja-listar', ['only' => ['index','show']]);
        $this->middleware('permission:caja-abrir', ['only' => ['abrir','store']]);

        $this->middleware('permission:caja-cerrar', ['only' => ['cerrar']]);
        $this->middleware('permission:caja-arqueo', ['only' => ['arqueo']]);

    }

    // Listado de cajas
    public function index()
    {
        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1');

        return view('cajas.index', compact('sucursals'));
    }

    public function dataTable(Request $request)
    {
        $columnas = [
            'cajas.apertura',
            'sucursals.nombre',
            'users.name',
            'cajas.inicial',
            'cajas.final',
            'cajas.estado'
        ];

        $columnaOrden = $columnas[$request->input('order.0.column', 0)];
        $orden = $request->input('order.0.dir', 'desc');
        $busqueda = $request->input('search.value');
        $sucursal_id = $request->input('sucursal_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $query = Caja::select(
            'cajas.id',
            'cajas.apertura',
            'cajas.inicial',
            'cajas.final',
            'cajas.estado',
            'sucursals.nombre as sucursal_nombre',
            'users.name as usuario_nombre'
        )
            ->leftJoin('sucursals', 'cajas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('users', 'cajas.user_id', '=', 'users.id');

        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('cajas.sucursal_id', $sucursal_id);
        }

        if (!empty($fechaDesde)) {
            $query->whereDate('cajas.apertura', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('cajas.apertura', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $columna) {
                    $q->orWhere($columna, 'like', "%$busqueda%");
                }
            });
        }

        $recordsFiltered = $query->count();
        $recordsTotal = Caja::count();

        $datos = $query->orderBy($columnaOrden, $orden)
            ->skip($request->input('start', 0))
            ->take($request->input('length', 10))
            ->get()
            ->transform(function($item){
                $item->inicial = $item->inicial ?? 0;
                $item->final = $item->final ?? 0;
                return $item;
            });

        // Totales
        $totalInicial = (clone $query)->sum('cajas.inicial');
        $totalFinal = (clone $query)->sum('cajas.final');

        return response()->json([
            'data' => $datos,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => (int) $request->draw,  // <--- forzar a número
            'totales' => [
                'totalInicial' => $totalInicial,
                'totalFinal' => $totalFinal,
            ]
        ]);
    }




    public function arqueoActual()
    {
        $caja = Caja::where('estado','abierta')->firstOrFail();
        return redirect()->route('cajas.arqueo', $caja->id);
    }


    // Abrir caja
    public function abrir()
    {
        // Obtener las sucursales disponibles para abrir caja
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('cajas.abrir', compact('sucursals'));
    }

    // Guardar caja abierta
    public function store(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursals,id',
            'inicial' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Crear la caja
            $caja = Caja::create([
                'sucursal_id' => $request->sucursal_id,
                'user_id' => auth()->id(),
                'apertura' => now(),
                'inicial' => $request->inicial,
                'estado' => 'abierta',
            ]);

            // Crear el movimiento de apertura
            $conceptoApertura = Concepto::firstOrCreate(['nombre' => 'Apertura']);
            MovimientoCaja::create([
                'caja_id' => $caja->id,
                'concepto_id' => $conceptoApertura->id,
                'medio_id' => null,
                'venta_id' => null,
                'tipo' => 'ingreso',
                'monto' => $request->inicial,
                'acreditado' => true,
                'fecha' => now(),
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('cajas.show', ['caja' => $caja->id])
                ->with('success', 'Caja abierta correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error al abrir la caja: ' . $e->getMessage());
        }
    }


    // Ver caja abierta
    public function show($id)
    {
        $caja = Caja::with(['movimientos.concepto','movimientos.medio','movimientos.venta'])->findOrFail($id);
        $conceptos = Concepto::where('activo',true)->get();
        $medios = Medio::where('activo',true)->get();
        return view('cajas.show', compact('caja','conceptos','medios'));
    }



    // Cerrar caja
    public function cerrar($id)
    {
        $caja = Caja::with('movimientos')->findOrFail($id);

        $totalIngresos = $caja->movimientos()->where('tipo','ingreso')->where('acreditado',true)->sum('monto');
        $totalEgresos  = $caja->movimientos()->where('tipo','egreso')->sum('monto');

        $caja->update([
            'final' => $caja->inicial + $totalIngresos - $totalEgresos,
            'cierre' => now(),
            'estado' => 'cerrada'
        ]);

        return redirect()->route('cajas.index')->with('success','Caja cerrada correctamente.');
    }

    // Mostrar arqueo de la caja
    public function arqueo($id)
    {
        $caja = Caja::with(['movimientos.concepto','movimientos.medio','movimientos.venta'])->findOrFail($id);

        // Totales separados por tipo y acreditado
        $totales = [
            'ingresosAcreditados' => $caja->movimientos()->where('tipo','ingreso')->where('acreditado',true)->sum('monto'),
            'ingresosPendientes' => $caja->movimientos()->where('tipo','ingreso')->where('acreditado',false)->sum('monto'),
            'egresos' => $caja->movimientos()->where('tipo','egreso')->sum('monto'),
        ];

        return view('cajas.arqueo', compact('caja','totales'));
    }


}
