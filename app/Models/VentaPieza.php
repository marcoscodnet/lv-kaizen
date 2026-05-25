<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaPieza extends Model
{
    use HasFactory;

    protected $fillable = ['precio','precio_minimo','cliente','documento','telefono','moto','sucursal_id','pedido','user_id','user_name','fecha','descripcion','destino','cliente_id','servicio_id','forma'];




    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function sucursal() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_id');
    }

    public function cliente() {
        return $this->belongsTo('App\Models\Cliente', 'cliente_id');
    }

    public function piezas()
    {
        return $this->hasMany(PiezaVentaPieza::class, 'venta_pieza_id');
    }

    public function pagos()
    {
        return $this->hasMany(\App\Models\Pago::class, 'venta_pieza_id');
    }

    public function autorizaciones()
    {
        return $this->hasManyThrough(
            \App\Models\Autorizacion::class,
            \App\Models\Pago::class,
            'venta_pieza_id', // FK en pagos
            'pago_id',        // FK en autorizacions
            'id',             // PK en venta_piezas
            'id'              // PK en pagos
        );
    }

}
