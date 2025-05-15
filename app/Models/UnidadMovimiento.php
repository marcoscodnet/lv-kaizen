<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMovimiento extends Model
{
    use HasFactory;

    protected $fillable = ['unidad_id', 'movimiento_id'];


    public function unidad() {
        return $this->belongsTo('App\Models\Unidad');
    }

    public function movimiento() {
        return $this->belongsTo('App\Models\Movimiento');
    }
}
