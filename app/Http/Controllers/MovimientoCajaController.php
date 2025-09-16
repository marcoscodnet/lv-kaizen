<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MovimientoCaja;
use App\Models\Caja;
use App\Models\Concepto;
use App\Models\Medio;
use DB;

class MovimientoCajaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:caja-movimiento-registrar', ['only' => ['create','store']]);
        $this->middleware('permission:caja-movimiento-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:caja-movimiento-eliminar', ['only' => ['destroy']]);
        $this->middleware('permission:caja-acreditar', ['only' => ['acreditar']]);
    }

    // Listado de movimientos (opcional)
    public function index()
    {
        $movimientos = MovimientoCaja::with(['caja', 'concepto', 'medio'])->latest()->get();
        return view('movimiento_cajas.index', compact('movimientos'));
    }

    // Formulario para crear un movimiento
    public function create($caja_id)
    {
        $caja = Caja::findOrFail($caja_id);
        $conceptos = Concepto::where('activo', true)->get();
        $medios = Medio::where('activo', true)->get();

        return view('movimiento_cajas.create', compact('caja', 'conceptos', 'medios'));
    }

    // Guardar movimiento
    public function store(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'concepto_id' => 'required|exists:conceptos,id',
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0',
            'medio_id' => 'nullable|exists:medios,id',
            'venta_id' => 'nullable|integer',
            'acreditado' => 'nullable|boolean',
            'referencia' => 'nullable|string|max:255'
        ]);

        DB::transaction(function () use ($request) {
            MovimientoCaja::create([
                'caja_id' => $request->caja_id,
                'concepto_id' => $request->concepto_id,
                'medio_id' => $request->medio_id,
                'venta_id' => $request->venta_id,
                'tipo' => $request->tipo,
                'monto' => $request->monto,
                'acreditado' => $request->acreditado ?? true,
                'fecha' => now(),
                'referencia' => $request->referencia,
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('cajas.show', $request->caja_id)
            ->with('success', 'Movimiento registrado correctamente.');
    }

    // Editar movimiento
    public function edit($movimiento_id)
    {
        $mov = MovimientoCaja::findOrFail($movimiento_id);
        $conceptos = Concepto::where('activo', true)->get();
        $medios = Medio::where('activo', true)->get();

        return view('movimiento_cajas.edit', compact('mov', 'conceptos', 'medios'));
    }

    // Actualizar movimiento
    public function update(Request $request, $movimiento_id)
    {
        $mov = MovimientoCaja::findOrFail($movimiento_id);

        $request->validate([
            'concepto_id' => 'required|exists:conceptos,id',
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0',
            'medio_id' => 'nullable|exists:medios,id',
            'venta_id' => 'nullable|integer',
            'acreditado' => 'nullable|boolean',
            'referencia' => 'nullable|string|max:255'
        ]);

        $mov->update($request->only(['concepto_id','medio_id','venta_id','tipo','monto','acreditado','referencia']));

        return redirect()->route('cajas.show', $mov->caja_id)
            ->with('success', 'Movimiento actualizado correctamente.');
    }

    // Eliminar movimiento
    public function destroy($movimiento_id)
    {
        $mov = MovimientoCaja::findOrFail($movimiento_id);
        $caja_id = $mov->caja_id;
        $mov->delete();

        return redirect()->route('cajas.show', $caja_id)
            ->with('success', 'Movimiento eliminado correctamente.');
    }

    // Acreditar movimiento
    public function acreditar($movimiento_id)
    {
        $mov = MovimientoCaja::findOrFail($movimiento_id);
        $mov->update(['acreditado' => true]);

        return redirect()->route('cajas.show', $mov->caja_id)
            ->with('success', 'Movimiento acreditado correctamente.');
    }
}
