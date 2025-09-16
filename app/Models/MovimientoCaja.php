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
        'concepto_id',
        'tipo',
        'monto',
        'medio',
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

    public function medio()
    {
        return $this->belongsTo(Medio::class);
    }
}
