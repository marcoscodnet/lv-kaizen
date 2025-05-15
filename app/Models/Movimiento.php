<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    protected $fillable = ['sucursal_origen_id','sucursal_destino_id','user_id','fecha','observaciones'];

    public function sucursalOrigen() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_origen_id');
    }

    public function sucursalDestino() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_destino_id');
    }
}
