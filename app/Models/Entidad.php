<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ticket',
        'referencia',
        'tangible',
        'activa'
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'medio_id');
    }
}
