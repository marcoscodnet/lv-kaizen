<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCuenta extends Model
{
    use HasFactory;
    protected $fillable = [
        'entidad_id', 'tipo', 'monto', 'fecha', 'concepto',
        'venta_id', 'venta_pieza_id', 'servicio_id', 'pago_id','transferencia_id',
        'user_id', 'observacion',
    ];

    public function entidad()   { return $this->belongsTo(Entidad::class); }
    public function venta()     { return $this->belongsTo(Venta::class); }
    public function ventaPieza(){ return $this->belongsTo(VentaPieza::class); }
    public function servicio()  { return $this->belongsTo(Servicio::class); }
    public function pago()      { return $this->belongsTo(Pago::class); }
    public function user()      { return $this->belongsTo(User::class); }
}
