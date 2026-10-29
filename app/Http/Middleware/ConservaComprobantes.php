<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Cuando una operación con cobro (venta, venta de artículos, servicio, seña)
 * vuelve al formulario por un error —caja cerrada, cobro incompleto, stock,
 * moto bloqueada, lo que sea—, el formulario se reconstruye con old(), pero el
 * navegador no deja volver a llenar un <input type="file">: los comprobantes
 * adjuntos se perdían y había que subirlos de nuevo.
 *
 * Este middleware detecta esa vuelta (un redirect que trae `_old_input`) y
 * guarda los comprobantes del request en un temporal. Los deja en old() como
 * `comprobantes_conservados`, indexados por la posición de la fila de pago, y
 * cobro.js los vuelve a cargar en su fila (se suben de nuevo con el submit).
 */
class ConservaComprobantes
{
    public const DIR = 'comprobantes_tmp';
    public const SESSION_KEY = 'comprobantes_tmp';

    private const EXTENSIONES = ['jpeg', 'jpg', 'png', 'pdf'];
    private const MAX_BYTES = 5 * 1024 * 1024;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!$response instanceof RedirectResponse || !$request->hasSession()) {
            return $response;
        }

        $old = $request->session()->get('_old_input');
        $uids = (array) $request->input('pago_uid', []);
        if (!is_array($old) || empty($uids)) {
            return $response;
        }

        $conservados = [];
        foreach (array_values($uids) as $i => $uid) {
            foreach ($this->archivosDeLaFila($request, $uid) as $file) {
                if ($desc = $this->guardarTemporal($request, $file)) {
                    $conservados[$i][] = $desc;
                }
            }
        }

        if ($conservados) {
            $old['comprobantes_conservados'] = $conservados;
            $request->session()->flash('_old_input', $old);
        }

        return $response;
    }

    private function archivosDeLaFila(Request $request, $uid): array
    {
        if ($uid === null || $uid === '') {
            return [];
        }
        $files = $request->file('comprobantes_' . $uid);
        if (empty($files)) {
            return [];
        }
        return is_array($files) ? $files : [$files];
    }

    private function guardarTemporal(Request $request, $file): ?array
    {
        if (!$file || !$file->isValid()) {
            return null;
        }
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, self::EXTENSIONES) || $file->getSize() > self::MAX_BYTES) {
            return null;
        }

        $dir = storage_path('app/' . self::DIR);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $this->limpiarViejos($dir);

        $token = Str::random(40);
        $nombre = $file->getClientOriginalName() ?: ('comprobante.' . $ext);
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $file->move($dir, $token . '.' . $ext);

        // Registro en la sesión: solo quien subió el archivo lo puede volver a bajar.
        $registro = $request->session()->get(self::SESSION_KEY, []);
        $registro[$token] = ['archivo' => $token . '.' . $ext, 'nombre' => $nombre, 'mime' => $mime];
        $request->session()->put(self::SESSION_KEY, $registro);

        return [
            'url'    => route('comprobantes.temporal', $token),
            'nombre' => $nombre,
            'mime'   => $mime,
        ];
    }

    // Los temporales sirven para la vuelta al formulario: a las 24 hs se borran.
    private function limpiarViejos(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $f) {
            if (is_file($f) && filemtime($f) < time() - 86400) {
                @unlink($f);
            }
        }
    }
}
