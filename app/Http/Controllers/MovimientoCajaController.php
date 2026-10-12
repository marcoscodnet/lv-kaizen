<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MovimientoCaja;
use App\Models\Caja;
use App\Models\Concepto;
use App\Models\Entidad;
use App\Models\Senia;
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
        $this->autorizarCaja($caja);
        $conceptos = Concepto::where('activo', true)->get();
        $entidads = Entidad::where('activa', true)->where('tangible', true)->get();

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
            'referencia' => 'nullable|string|max:255',
            'cliente_id' => 'nullable|exists:clientes,id',
        ]);

        $caja = Caja::findOrFail($request->caja_id);
        $this->autorizarCaja($caja);

        // Seña: ingreso atado a un cliente, que después se aplica a la venta
        $esSenia = (int) $request->concepto_id === (int) Senia::conceptoId();
        if ($esSenia) {
            $errores = [];
            if (!$request->filled('cliente_id')) {
                $errores[] = 'Para registrar una seña hay que elegir el cliente.';
            }
            if ($request->tipo !== 'Ingreso') {
                $errores[] = 'Una seña es siempre un ingreso.';
            }
            if (!$request->filled('entidad_id')) {
                $errores[] = 'Para registrar una seña hay que elegir la forma de pago.';
            }
            if ($caja->estado !== 'Abierta') {
                $errores[] = 'La seña se registra en una caja abierta.';
            }
            if ($errores) {
                return redirect()->back()->withErrors($errores)->withInput();
            }
        }

        DB::transaction(function () use ($request, $esSenia, $caja) {
            $mov = MovimientoCaja::create([
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

            if ($esSenia) {
                Senia::create([
                    'movimiento_caja_id' => $mov->id,
                    'cliente_id'         => $this->sanitizeInput($request->cliente_id),
                    'sucursal_id'        => $caja->sucursal_id,
                    'monto'              => $mov->monto,
                    'fecha'              => $mov->fecha,
                    'estado'             => Senia::PENDIENTE,
                    'observacion'        => $mov->referencia,
                    'user_id'            => auth()->id(),
                ]);
            }
        });

        return redirect()->route('cajas.show', $request->caja_id)
            ->with('success', $esSenia ? 'Seña registrada correctamente.' : 'Movimiento registrado correctamente.');
    }

    // Editar movimiento
    public function edit($movimiento_id)
    {

        $mov = MovimientoCaja::with('caja')->findOrFail($movimiento_id);
        $this->autorizarCaja($mov->caja);

        // 🔒 Bloquear si la caja ya está cerrada
        if ($mov->caja->estado === 'Cerrada') {
            return redirect()->back()->withErrors('No se pueden editar movimientos de una caja cerrada.');
        }
        // 🔒 Las señas no se editan: se eliminan (si siguen pendientes) o se devuelven
        if ($mov->senia) {
            return redirect()->back()->withErrors('Una seña no se edita. Si está pendiente, eliminala y cargala de nuevo, o devolvela desde Señas.');
        }

        //dd($mov);
        // 🔒 Bloquear si ya está acreditado
        if ($mov->acreditado) {
            return redirect()->back()->withErrors('No se pueden editar movimientos acreditados.');
        }

        $conceptos = Concepto::where('activo', true)->get();
        $entidads = Entidad::where('activa', true)->where('tangible', true)->get();

        return view('movimiento_cajas.edit', compact('mov', 'conceptos', 'entidads'));
    }


    // Actualizar movimiento
    public function update(Request $request, $movimiento_id)
    {
        $mov = MovimientoCaja::with('caja')->findOrFail($movimiento_id);
        $this->autorizarCaja($mov->caja);

        // 🔒 Validaciones de negocio
        if ($mov->caja->estado === 'Cerrada') {
            return redirect()->route('cajas.show', $mov->caja_id)
                ->withErrors('No se pueden editar movimientos de una caja cerrada.');
        }

        if ($mov->acreditado) {
            return redirect()->route('cajas.show', $mov->caja_id)
                ->withErrors('No se pueden editar movimientos ya acreditados.');
        }

        if ($mov->senia || (int) $request->concepto_id === (int) Senia::conceptoId()) {
            return redirect()->route('cajas.show', $mov->caja_id)
                ->withErrors('Las señas se registran desde "Registrar Movimiento" y no se editan.');
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
        $this->autorizarCaja($mov->caja);

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

        // Una seña ya aplicada a una venta o dada de baja no se puede borrar
        if ($mov->senia && !$mov->senia->esPendiente()) {
            return redirect()->route('cajas.show', $mov->caja_id)
                ->withErrors('No se puede eliminar una seña ' . strtolower($mov->senia->estado) . '.');
        }

        $caja_id = $mov->caja_id;
        $mov->delete(); // la seña pendiente se borra en cascada

        return redirect()->route('cajas.show', $caja_id)
            ->with('success', 'Movimiento eliminado correctamente.');
    }


    // Acreditar movimiento
    public function acreditar($movimiento_id)
    {
        $mov = MovimientoCaja::findOrFail($movimiento_id);
        $this->autorizarCaja($mov->caja);
        $mov->update(['acreditado' => true]);

        return redirect()->route('cajas.show', $mov->caja_id)
            ->with('success', 'Movimiento acreditado correctamente.');
    }

    // 403 si la caja no es de la sucursal del usuario (salvo Administrador).
    private function autorizarCaja($caja): void
    {
        if (!$caja || !$caja->esVisiblePara(auth()->user())) {
            abort(403, 'No tenés acceso a las cajas de otra sucursal.');
        }
    }
}
