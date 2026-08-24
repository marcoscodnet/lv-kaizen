<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ticket',
        'referencia',
        'autorizacion',
        'tangible',
        'activa',
        'forma',
        'cuenta'
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'medio_id');
    }

    /**
     * Entidades que no pasan por auditoría (efectivo y similares).
     *
     * Auditoría solo lista pagos con entidads.autorizacion = 1, así que un pago
     * de una entidad sin autorización nunca se podría acreditar a mano: se
     * acredita solo al registrarse, por el importe cobrado, y no lleva fecha
     * de contadora.
     */
    public function acreditaAutomatico(): bool
    {
        return !$this->autorizacion;
    }
}
