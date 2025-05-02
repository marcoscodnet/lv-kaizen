<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;

    protected $fillable = ['producto_id','sucursal_id','motor','cuadro','patente','remito','year','envio','ingreso','observaciones'];



    public function producto() {
        return $this->belongsTo('App\Models\Producto', 'producto_id');
    }

    public function sucursal() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_id');
    }


}
