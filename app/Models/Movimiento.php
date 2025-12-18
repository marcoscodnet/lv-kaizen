<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    protected $fillable = ['sucursal_origen_id','sucursal_destino_id','user_id','fecha','observaciones','estado','aceptado','user_acepta_id'];

    public function sucursalOrigen() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_origen_id');
    }

    public function sucursalDestino() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_destino_id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function user_acepta() {
        return $this->belongsTo('App\Models\User', 'user_acepta_id');
    }

    public function unidadMovimientos()
    {
        return $this->hasMany(UnidadMovimiento::class, 'movimiento_id');
    }

}
