<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPieza extends Model
{
    use HasFactory;

    protected $fillable = ['pieza_id','sucursal_id','remito','cantidad','costo','precio_minimo','proveedor','ingreso'];


    public function pieza() {
        return $this->belongsTo('App\Models\Pieza', 'pieza_id');
    }

    public function sucursal() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_id');
    }
}
