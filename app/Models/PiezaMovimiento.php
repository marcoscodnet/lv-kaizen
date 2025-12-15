<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiezaMovimiento extends Model
{
    use HasFactory;

    protected $fillable = ['unidad_id', 'movimientoPieza_id','cantidad'];


    public function pieza() {
        return $this->belongsTo('App\Models\Pieza');
    }

    public function movimientoPieza() {
        return $this->belongsTo('App\Models\MovimientoPieza');
    }
}
