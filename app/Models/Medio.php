<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medio extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'ticket',
        'referencia',
        'tangible',
        'activo'
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'medio_id');
    }
}
