<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiezaVentaPieza extends Model
{
    use HasFactory;

    protected $fillable = ['pieza_id', 'sucursal_id','pieza_venta_id','cantidad','precio'];


    public function pieza() {
        return $this->belongsTo('App\Models\Pieza');
    }

    public function sucursal() {
        return $this->belongsTo('App\Models\Sucursal');
    }

    public function pieza_venta() {
        return $this->belongsTo('App\Models\PiezaVentaPieza');
    }
}
