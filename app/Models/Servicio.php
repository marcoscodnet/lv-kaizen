<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = ['carga','tipo_servicio_id','cliente_id','sucursal_id','kilometros','ingreso','observacion','descripcion','diagnostico','repuestos','mecanicos','instrumentos','tiempo','entrega','monto','pagado','modelo','year','chasis','motor','venta','user_id','mano_de_obra','costo_repuestos','forma','marca_id','modelo_id'];


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

    public function ventaPiezas()
    {
        return $this->hasMany(\App\Models\VentaPieza::class, 'servicio_id');
    }

// Calculate total parts cost from linked sales
    public function getCostoRepuestosAttribute(): float
    {
        return $this->ventaPiezas()
            ->join('pieza_venta_piezas', 'pieza_venta_piezas.venta_pieza_id', '=', 'venta_piezas.id')
            ->sum('pieza_venta_piezas.precio');
    }

    public function pagos()
    {
        return $this->hasMany(\App\Models\Pago::class, 'servicio_id');
    }

    public function marca() {
        return $this->belongsTo('App\Models\Marca', 'marca_id');
    }

    public function modelo() {
        return $this->belongsTo('App\Models\Modelo', 'modelo_id');
    }

}
