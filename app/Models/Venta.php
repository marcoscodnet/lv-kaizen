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

    public function cliente() {
        return $this->belongsTo('App\Models\Cliente', 'cliente_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }

    /**
     * Venta de artículos que cuelga de esta venta: patentamiento, seguro, casco
     * y lo que se haya cargado en la misma pantalla. No tiene pagos propios —
     * la plata vive toda acá.
     */
    public function ventaArticulos()
    {
        return $this->hasOne(VentaPieza::class, 'venta_id');
    }

    /** Importe de los conceptos cargados junto con la moto. */
    public function getTotalArticulosAttribute(): float
    {
        $vp = $this->relationLoaded('ventaArticulos')
            ? $this->getRelation('ventaArticulos')
            : $this->ventaArticulos;

        if (!$vp) {
            return 0.0;
        }

        return (float) $vp->piezas->sum(function ($p) {
            return (float) $p->precio * (float) ($p->cantidad ?: 1);
        });
    }

    /**
     * Lo que hay que cobrarle al cliente por toda la operación: la moto más los
     * conceptos. Es el número contra el que se compara lo acreditado.
     *
     * Se calcula en vivo y no se lee de `total` para que nunca quede desfasado
     * si alguien toca los artículos por fuera.
     */
    public function getTotalACobrarAttribute(): float
    {
        return (float) $this->monto + $this->total_articulos;
    }

    public function autorizaciones()
    {
        return $this->hasManyThrough(
            \App\Models\Autorizacion::class,
            \App\Models\Pago::class,
            'venta_id', // FK in pagos
            'pago_id',  // FK in autorizacions
            'id',       // PK in ventas
            'id'        // PK in pagos
        );
    }
}
