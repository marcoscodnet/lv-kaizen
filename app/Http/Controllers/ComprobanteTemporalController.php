<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ConservaComprobantes;
use Illuminate\Http\Request;

/**
 * Devuelve un comprobante guardado temporalmente por ConservaComprobantes, para
 * que cobro.js lo vuelva a cargar en su fila de pago. Solo lo puede pedir la
 * misma sesión que lo subió.
 */
class ComprobanteTemporalController extends Controller
{
    public function show(Request $request, string $token)
    {
        $registro = $request->session()->get(ConservaComprobantes::SESSION_KEY, []);
        abort_unless(isset($registro[$token]), 404);

        $path = storage_path('app/' . ConservaComprobantes::DIR . '/' . $registro[$token]['archivo']);
        abort_unless(is_file($path), 404);

        return response()->file($path, ['Content-Type' => $registro[$token]['mime']]);
    }
}
