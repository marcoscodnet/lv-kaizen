<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPieza extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'maneja_stock'];

    protected $casts = [
        'maneja_stock' => 'boolean',
    ];

    /**
     * Tipos que no llevan existencias: patentamiento, seguro y demás conceptos
     * que se cobran pero no se tienen en depósito. No validan ni descuentan
     * stock, y se pueden seleccionar en cualquier sucursal.
     */
    public function scopeSinStock($query)
    {
        return $query->where('maneja_stock', 0);
    }
}
