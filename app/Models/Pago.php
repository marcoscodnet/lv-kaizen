<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = ['venta_id','entidad_id','monto','fecha','pagado','contadora','detalle','observacion'];




    public function venta() {
        return $this->belongsTo('App\Models\Venta', 'venta_id');
    }

    public function entidad() {
        return $this->belongsTo('App\Models\Entidad', 'entidad_id');
    }


}
