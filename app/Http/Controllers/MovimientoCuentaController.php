<?php
// app/Http/Controllers/MovimientoCuentaController.php

namespace App\Http\Controllers;

use App\Models\Entidad;
use App\Models\MovimientoCuenta;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;

class MovimientoCuentaController extends Controller
{
    use SanitizesInput;

    function __construct()
    {
        $this->middleware('permission:cuenta-listar|cuenta-crear', ['only' => ['index', 'show', 'dataTable']]);
        $this->middleware('permission:cuenta-crear', ['only' => ['store']]);
        $this->middleware('permission:cuenta-eliminar', ['only' => ['destroy']]);
    }

    // List all non-tangible entities with their current balance
    public function index()
    {
        $entidades = Entidad::where('cuenta', true)
            ->where('activa', 1)
            ->orderBy('nombre')
            ->get()
            ->map(function ($e) {
                $ingresos    = MovimientoCuenta::where('entidad_id', $e->id)
                    ->where('tipo', 'Ingreso')->sum('monto');
                $egresos     = MovimientoCuenta::where('entidad_id', $e->id)
                    ->where('tipo', 'Egreso')->sum('monto');
                $e->saldo    = $ingresos - $egresos;
                $e->ingresos = $ingresos;
                $e->egresos  = $egresos;
                return $e;
            });

        return view('cuentas.index', compact('entidades'));
    }

    // Detail view for one entity account
    public function show($id)
    {
        $entidad = Entidad::where('cuenta', true)
            ->where('activa', 1)
            ->findOrFail($id);

        return view('cuentas.show', compact('entidad'));
    }

    // DataTable for movements of one entity account
    public function dataTable(Request $request, $id)
    {
        $entidad = Entidad::findOrFail($id);

        $columnas = [
            'movimiento_cuentas.fecha',
            'movimiento_cuentas.concepto',
            'movimiento_cuentas.tipo',
            'movimiento_cuentas.monto',
            'users.name',
        ];

        $columnaOrden = $columnas[$request->input('order.0.column')] ?? 'movimiento_cuentas.fecha';
        $orden        = $request->input('order.0.dir', 'desc');
        $busqueda     = $request->input('search.value');
        $fechaDesde   = $request->input('fecha_desde');
        $fechaHasta   = $request->input('fecha_hasta');

        $query = MovimientoCuenta::select(
            'movimiento_cuentas.id',
            'movimiento_cuentas.fecha',
            'movimiento_cuentas.concepto',
            'movimiento_cuentas.tipo',
            'movimiento_cuentas.monto',
            'movimiento_cuentas.observacion',
            'movimiento_cuentas.venta_id',
            'movimiento_cuentas.venta_pieza_id',
            'movimiento_cuentas.servicio_id',
            'movimiento_cuentas.pago_id',
            'movimiento_cuentas.transferencia_id',
            'users.name as usuario_nombre'
        )
            ->leftJoin('users', 'movimiento_cuentas.user_id', '=', 'users.id')
            ->where('movimiento_cuentas.entidad_id', $id);

        if (!empty($fechaDesde)) {
            $query->whereDate('movimiento_cuentas.fecha', '>=', $fechaDesde);
        }
        if (!empty($fechaHasta)) {
            $query->whereDate('movimiento_cuentas.fecha', '<=', $fechaHasta);
        }
        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('movimiento_cuentas.concepto', 'like', "%$busqueda%")
                    ->orWhere('movimiento_cuentas.tipo', 'like', "%$busqueda%")
                    ->orWhere('users.name', 'like', "%$busqueda%")
                    ->orWhere('movimiento_cuentas.observacion', 'like', "%$busqueda%");
            });
        }

        $base = clone $query;

        $totalIngresos = (clone $base)
            ->where('movimiento_cuentas.tipo', 'Ingreso')
            ->sum('movimiento_cuentas.monto');

        $totalEgresos = (clone $base)
            ->where('movimiento_cuentas.tipo', 'Egreso')
            ->sum('movimiento_cuentas.monto');

        $saldo = $totalIngresos - $totalEgresos;

        $recordsFiltered = (clone $base)->count();
        $recordsTotal    = MovimientoCuenta::where('entidad_id', $id)->count();

        $datos = (clone $base)
            ->orderBy($columnaOrden, $orden)
            ->skip($request->input('start', 0))
            ->take($request->input('length', 10))
            ->get();

        return response()->json([
            'data'            => $datos,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw'            => $request->draw,
            'totales' => [
                'ingresos' => $totalIngresos,
                'egresos'  => $totalEgresos,
                'saldo'    => $saldo,
            ],
        ]);
    }

    // Store a manual movement
    public function store(Request $request, $id)
    {
        $entidad = Entidad::where('tangible', 0)
            ->where('activa', 1)
            ->findOrFail($id);

        $request->validate([
            'tipo'     => 'required|in:Ingreso,Egreso',
            'monto'    => 'required|numeric|min:0.01',
            'fecha'    => 'required|date',
            'concepto' => 'required|string|max:255',
        ]);

        $input = $this->sanitizeInput($request->all());

        MovimientoCuenta::create([
            'entidad_id'  => $entidad->id,
            'tipo'        => $input['tipo'],
            'monto'       => $input['monto'],
            'fecha'       => $input['fecha'],
            'concepto'    => $input['concepto'],
            'observacion' => $input['observacion'] ?? null,
            'user_id'     => auth()->id(),
        ]);

        return redirect()->route('cuentas.show', $id)
            ->with('success', 'Movimiento registrado con éxito');
    }

    // Delete a manual movement (only allows deleting movements without origin)
    public function destroy($id)
    {
        $movimiento = MovimientoCuenta::findOrFail($id);

        // Only allow deletion of manually created movements
        if ($movimiento->venta_id || $movimiento->venta_pieza_id || $movimiento->servicio_id) {
            return redirect()->back()
                ->with('error', 'No se pueden eliminar movimientos generados automáticamente');
        }

        $entidadId = $movimiento->entidad_id;
        $movimiento->delete();

        return redirect()->route('cuentas.show', $entidadId)
            ->with('success', 'Movimiento eliminado con éxito');
    }

    public function storeTransferencia(Request $request, $id)
    {
        $entidadOrigen = Entidad::where('tangible', 0)
            ->where('activa', 1)
            ->findOrFail($id);

        $request->validate([
            'entidad_destino_id' => 'required|different:' . $id . '|exists:entidades,id',
            'monto'              => 'required|numeric|min:0.01',
            'fecha'              => 'required|date',
            'observacion'        => 'nullable|string|max:500',
        ], [
            'entidad_destino_id.required'  => 'Debe seleccionar una cuenta destino.',
            'entidad_destino_id.different' => 'La cuenta destino debe ser distinta a la cuenta origen.',
            'monto.required'               => 'El monto es obligatorio.',
            'fecha.required'               => 'La fecha es obligatoria.',
        ]);

        $entidadDestino = Entidad::where('tangible', 0)
            ->where('activa', 1)
            ->findOrFail($request->entidad_destino_id);

        DB::beginTransaction();
        try {
            // Egreso from origin account
            $egreso = MovimientoCuenta::create([
                'entidad_id'  => $entidadOrigen->id,
                'tipo'        => 'Egreso',
                'monto'       => $request->monto,
                'fecha'       => $request->fecha,
                'concepto'    => 'Transferencia a ' . $entidadDestino->nombre,
                'observacion' => $request->observacion,
                'user_id'     => auth()->id(),
            ]);

            // Ingreso in destination account
            $ingreso = MovimientoCuenta::create([
                'entidad_id'     => $entidadDestino->id,
                'tipo'           => 'Ingreso',
                'monto'          => $request->monto,
                'fecha'          => $request->fecha,
                'concepto'       => 'Transferencia desde ' . $entidadOrigen->nombre,
                'observacion'    => $request->observacion,
                'transferencia_id' => $egreso->id,
                'user_id'        => auth()->id(),
            ]);

            // Link egreso to ingreso
            $egreso->transferencia_id = $ingreso->id;
            $egreso->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar la transferencia: ' . $e->getMessage());
        }

        return redirect()->route('cuentas.show', $id)
            ->with('success', 'Transferencia registrada con éxito');
    }
}
