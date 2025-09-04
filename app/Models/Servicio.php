<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = ['carga','tipo_servicio_id','cliente_id','sucursal_id','kilometros','ingreso','observacion','descripcion','diagnostico','repuestos','mecanicos','instrumentos','tiempo','entrega','monto','pagado','modelo','year','chasis','motor','venta','user_id'];


    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function sucursal() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_id');
    }

    public function tipoServicio() {
        return $this->belongsTo('App\Models\TipoServicio', 'tipo_servicio_id');
    }

    public function cliente() {
        return $this->belongsTo('App\Models\Cliente', 'cliente_id');
    }



}
