<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MovimientoCaja;
use App\Models\Caja;
use App\Models\Concepto;
use App\Models\Entidad;
use DB;
use App\Traits\SanitizesInput;
class MovimientoCajaController extends Controller
{
    use SanitizesInput;
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
        $movimientos = MovimientoCaja::with(['caja', 'concepto', 'entidad'])->latest()->get();
        return view('movimiento_cajas.index', compact('movimientos'));
    }

    // Formulario para crear un movimiento
    public function create($caja_id)
    {
        $caja = Caja::findOrFail($caja_id);
        $conceptos = Concepto::where('activo', true)->get();
        $entidads = Entidad::where('activa', true)->get();

        return view('movimiento_cajas.create', compact('caja', 'conceptos', 'entidads'));
    }

    // Guardar movimiento
    public function store(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'concepto_id' => 'required|exists:conceptos,id',
            'tipo' => 'required|in:Ingreso,Egreso',
            'monto' => 'required|numeric|min:0',
            'entidad_id' => 'nullable|exists:entidads,id',
            'venta_id' => 'nullable|integer',
            'acreditado' => 'nullable|boolean',
            'referencia' => 'nullable|string|max:255'
        ]);

        DB::transaction(function () use ($request) {
            MovimientoCaja::create([
                'caja_id' => $this->sanitizeInput($request->caja_id),
                'concepto_id' => $this->sanitizeInput($request->concepto_id),
                'entidad_id' => $this->sanitizeInput($request->entidad_id),
                'venta_id' => $this->sanitizeInput($request->venta_id),
                'tipo' => $this->sanitizeInput($request->tipo),
                'monto' => $this->sanitizeInput($request->monto),
                'acreditado' => $request->acreditado ?? true,
                'fecha' => now(),
                'referencia' => $this->sanitizeInput($request->referencia),
                'user_id' => auth()->id(),
            ]);

        });

        return redirect()->route('cajas.show', $request->caja_id)
            ->with('success', 'Movimiento registrado correctamente.');
    }

    // Editar movimiento
    public function edit($movimiento_id)
    {

        $mov = MovimientoCaja::with('caja')->findOrFail($movimiento_id);

        // 🔒 Bloquear si la caja ya está cerrada
        if ($mov->caja->estado === 'Cerrada') {
            return redirect()->back()->withErrors('No se pueden editar movimientos de una caja cerrada.');
        }

        // 🔒 Bloquear si ya está acreditado
        if ($mov->acreditado) {
            return redirect()->back()->withErrors('No se pueden editar movimientos acreditados.');
        }

        $conceptos = Concepto::where('activo', true)->get();
        $entidads = Entidad::where('activa', true)->get();

        return view('movimiento_cajas.edit', compact('mov', 'conceptos', 'entidads'));
    }


    // Actualizar movimiento
    public function update(Request $request, $movimiento_id)
    {
        $mov = MovimientoCaja::with('caja')->findOrFail($movimiento_id);

        // 🔒 Validaciones de negocio
        if ($mov->caja->estado === 'Cerrada') {
            return redirect()->route('cajas.show', $mov->caja_id)
                ->withErrors('No se pueden editar movimientos de una caja cerrada.');
        }

        if ($mov->acreditado) {
            return redirect()->route('cajas.show', $mov->caja_id)
                ->withErrors('No se pueden editar movimientos ya acreditados.');
        }

        // Validación de datos
        $request->validate([
            'concepto_id' => 'required|exists:conceptos,id',
            'tipo' => 'required|in:Ingreso,Egreso',
            'monto' => 'required|numeric|min:0',
            'entidad_id' => 'nullable|exists:entidads,id',
            'venta_id' => 'nullable|integer',
            'acreditado' => 'nullable|boolean',
            'referencia' => 'nullable|string|max:255',
        ]);

        // Actualización
        $mov->update([
            'concepto_id' => $this->sanitizeInput($request->concepto_id),
            'entidad_id' => $this->sanitizeInput($request->entidad_id),
            'venta_id' => $this->sanitizeInput($request->venta_id),
            'tipo' => $this->sanitizeInput($request->tipo),
            'monto' => $this->sanitizeInput($request->monto),
            'acreditado' => $request->acreditado,
            'referencia' => $this->sanitizeInput($request->referencia),
        ]);


        return redirect()->route('cajas.show', $mov->caja_id)
            ->with('success', 'Movimiento actualizado correctamente.');
    }


    // Eliminar movimiento
    public function destroy($movimiento_id)
    {
        $mov = MovimientoCaja::with('caja')->findOrFail($movimiento_id);

        // Bloquear si la caja está cerrada
        if ($mov->caja->estado === 'Cerrada') {
            return redirect()->route('cajas.show', $mov->caja_id)
                ->withErrors('No se pueden eliminar movimientos de una caja cerrada.');
        }

        // Bloquear si el movimiento ya está acreditado
        if ($mov->acreditado) {
            return redirect()->route('cajas.show', $mov->caja_id)
                ->withErrors('No se pueden eliminar movimientos acreditados.');
        }

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
