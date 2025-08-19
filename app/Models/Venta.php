<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','user_name','cliente_id','sucursal_id','unidad_id','monto','total','fecha','forma','observacion'];


    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function sucursal() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_id');
    }

    public function unidad() {
        return $this->belongsTo('App\Models\Unidad', 'unidad_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }
}
