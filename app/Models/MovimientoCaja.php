<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $fillable = [
        'caja_id',
        'venta_id',
        'venta_pieza_id',
        'servicio_id',
        'concepto_id',
        'tipo',
        'monto',
        'entidad_id',
        'referencia',
        'acreditado',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }

    public function entidad()
    {
        return $this->belongsTo(Entidad::class);
    }

    public function ventaPieza()
    {
        return $this->belongsTo(VentaPieza::class, 'venta_pieza_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Etiqueta del origen para los listados contables de caja, identificando
     * el documento: unidad -> nro de motor; pieza -> codigo - descripcion;
     * servicio -> cliente / unidad (marca modelo motor).
     */
    public function getOrigenAttribute(): string
    {
        // Venta de unidad -> nro de motor
        if ($this->venta_id) {
            $motor = optional(optional($this->venta)->unidad)->motor;
            return 'Unidad' . ($motor ? ' — Motor ' . $motor : '');
        }

        // Venta de piezas -> codigo - descripcion (una o varias)
        if ($this->venta_pieza_id) {
            $vp = $this->ventaPieza;
            $detalle = $vp
                ? $vp->piezas->map(function ($pvp) {
                    $p = $pvp->pieza;
                    return $p ? trim($p->codigo . ' - ' . $p->descripcion) : null;
                })->filter()->implode(', ')
                : '';
            return 'Pieza' . ($detalle ? ' — ' . $detalle : '');
        }

        // Servicio -> cliente / unidad (marca modelo motor)
        if ($this->servicio_id) {
            $s = $this->servicio;
            if (!$s) {
                return 'Servicio';
            }

            $cliente = optional($s->cliente)->nombre;

            // Ojo: 'modelo' es tambien una columna string en servicios, por eso
            // se resuelve el nombre del modelo por su id con fallback al string.
            $marcaNombre  = optional($s->marca)->nombre;
            $modeloNombre = $s->modelo_id
                ? optional(Modelo::find($s->modelo_id))->nombre
                : null;
            $modeloNombre = $modeloNombre ?: $s->modelo;

            $unidad = trim(implode(' ', array_filter([
                $marcaNombre,
                $modeloNombre,
                $s->motor,
            ])));

            $partes = array_filter([$cliente, $unidad ?: null]);
            return 'Servicio' . (count($partes) ? ' — ' . implode(' / ', $partes) : '');
        }

        return '-';
    }
}
