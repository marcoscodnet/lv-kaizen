<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'fecha',
        'user_id',
        'apertura',
        'cierre',
        'inicial',
        'final',
        'estado',
    ];

    protected $dates = ['fecha', 'apertura', 'cierre']; // <-- esto convierte automáticamente a Carbon

    /**
     * Caja abierta del día para una sucursal.
     * Regla de negocio: una sola caja por sucursal y por día, compartida
     * por todos los usuarios asignados a esa sucursal.
     */
    public static function abiertaDelDia($sucursalId, $fecha = null)
    {
        $fecha = $fecha ?: now()->toDateString();

        return static::where('sucursal_id', $sucursalId)
            ->whereDate('fecha', $fecha)
            ->where('estado', 'Abierta')
            ->first();
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class);
    }
}
