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
     * Cada usuario ve solo las cajas de su sucursal. El Administrador ve todas.
     */
    public static function veTodasLasSucursales($user): bool
    {
        return $user && $user->hasRole('Administrador');
    }

    public function scopeVisiblesPara($query, $user)
    {
        if (!static::veTodasLasSucursales($user)) {
            $query->where('cajas.sucursal_id', optional($user)->sucursal_id ?? 0);
        }
        return $query;
    }

    public function esVisiblePara($user): bool
    {
        return static::veTodasLasSucursales($user)
            || ($user && $user->sucursal_id && (int)$user->sucursal_id === (int)$this->sucursal_id);
    }

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

    /**
     * Cierra la caja calculando el monto final.
     *
     * final = efectivo total en la caja al cerrar: ingresos acreditados (incluye
     * el fondo inicial, registrado como movimiento de "Apertura") menos egresos.
     * Lo usan el cierre manual (CajaController@cerrar) y el automático
     * (comando cajas:cerrar-vencidas), para que ambos hagan la misma cuenta.
     */
    public function cerrar(): self
    {
        $ingresosAcreditados = $this->movimientos()
            ->where('tipo', 'Ingreso')->where('acreditado', true)->sum('monto');
        $egresos = $this->movimientos()->where('tipo', 'Egreso')->sum('monto');

        $this->final = $ingresosAcreditados - $egresos;
        $this->cierre = now();
        $this->estado = 'Cerrada';
        $this->save();

        return $this;
    }
}
