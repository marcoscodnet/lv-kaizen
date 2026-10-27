<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;

    // Los campos del bloqueo NO van en fillable a propósito: solo se tocan
    // desde bloquear() / desbloquear(), nunca desde el formulario de edición.
    protected $fillable = ['producto_id','sucursal_id','motor','cuadro','patente','remito','year','envio','ingreso','observaciones'];

    protected $casts = [
        'bloqueada'     => 'boolean',
        'bloqueo_fecha' => 'datetime',
    ];

    public function producto() {
        return $this->belongsTo('App\Models\Producto', 'producto_id');
    }

    public function sucursal() {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_id');
    }

    /** Usuario que bloqueó la unidad para la venta. */
    public function bloqueoUser() {
        return $this->belongsTo(User::class, 'bloqueo_user_id');
    }

    /* ---------- Bloqueo para la venta ---------- */

    public function estaVendida(): bool
    {
        return Venta::where('unidad_id', $this->id)->exists();
    }

    /** Desbloquea el usuario que la bloqueó o un Administrador. */
    public static function puedeDesbloquearComo($user, $bloqueoUserId): bool
    {
        return $user && ($user->hasRole('Administrador') || (int) $user->id === (int) $bloqueoUserId);
    }

    public function puedeDesbloquear($user): bool
    {
        return $this->bloqueada && static::puedeDesbloquearComo($user, $this->bloqueo_user_id);
    }

    /** "Bloqueada: Sin certificado (Juan, 23/10/2026)" */
    public function textoBloqueo(): string
    {
        if (!$this->bloqueada) {
            return '';
        }
        $quien = optional($this->bloqueoUser)->name;
        $cuando = $this->bloqueo_fecha ? $this->bloqueo_fecha->format('d/m/Y') : null;
        $detalle = implode(', ', array_filter([$quien, $cuando]));

        return 'Bloqueada: ' . $this->motivo_bloqueo . ($detalle ? ' (' . $detalle . ')' : '');
    }
}
