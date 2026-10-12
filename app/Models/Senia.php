<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Seña de una moto.
 *
 * Nace como un ingreso en la caja del día (MovimientoCaja con concepto "Seña")
 * atado a un cliente. Después, o se aplica como pago de una venta (estado
 * Aplicada, con un Pago de forma de pago "Seña" que no vuelve a tocar la caja),
 * o se da de baja: Devuelta (egreso en la caja del día) o Retenida (la plata
 * queda, no se mueve caja).
 */
class Senia extends Model
{
    public const PENDIENTE = 'Pendiente';
    public const APLICADA  = 'Aplicada';
    public const DEVUELTA  = 'Devuelta';
    public const RETENIDA  = 'Retenida';

    public const CONCEPTO            = 'Seña';
    public const CONCEPTO_DEVOLUCION = 'Devolución de seña';
    public const ENTIDAD             = 'Seña';

    protected $fillable = [
        'movimiento_caja_id', 'cliente_id', 'sucursal_id', 'monto', 'fecha', 'estado',
        'venta_id', 'pago_id', 'movimiento_devolucion_id', 'fecha_baja', 'observacion', 'user_id',
    ];

    protected $casts = [
        'fecha'      => 'datetime',
        'fecha_baja' => 'datetime',
    ];

    public function movimientoCaja()
    {
        return $this->belongsTo(MovimientoCaja::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function esPendiente(): bool
    {
        return $this->estado === self::PENDIENTE;
    }

    public static function conceptoId(): ?int
    {
        return optional(Concepto::where('nombre', self::CONCEPTO)->first())->id;
    }

    public static function conceptoDevolucion(): Concepto
    {
        return Concepto::firstOrCreate(
            ['nombre' => self::CONCEPTO_DEVOLUCION],
            ['tipo' => 'Resta', 'activo' => 0]
        );
    }

    public static function entidad(): Entidad
    {
        return Entidad::firstOrCreate(
            ['nombre' => self::ENTIDAD],
            ['forma' => 'Contado', 'autorizacion' => 0, 'tangible' => 0, 'cuenta' => 0, 'activa' => 0]
        );
    }

    /** Quien tiene el permiso ve y aplica señas de todas las sucursales. */
    public static function veTodasLasSucursales($user): bool
    {
        return $user && ($user->hasRole('Administrador') || $user->can('senia-otras-sucursales'));
    }

    public function scopeVisiblesPara($query, $user)
    {
        if (!static::veTodasLasSucursales($user)) {
            $query->where('senias.sucursal_id', optional($user)->sucursal_id ?? 0);
        }
        return $query;
    }

    public function esVisiblePara($user): bool
    {
        return static::veTodasLasSucursales($user)
            || ($user && $user->sucursal_id && (int) $user->sucursal_id === (int) $this->sucursal_id);
    }

    /**
     * Señas que se pueden aplicar a una venta de este cliente: las pendientes
     * y, al editar, las que ya están aplicadas a esa misma venta.
     */
    public static function aplicablesA($clienteId, $ventaId, $user)
    {
        // La restricción por sucursal vale para elegir señas pendientes; las que
        // ya están aplicadas a esta venta se muestran siempre, si no un usuario
        // de otra sucursal las liberaría sin querer al editar.
        return static::with('movimientoCaja.caja.sucursal', 'sucursal')
            ->where('cliente_id', $clienteId)
            ->where(function ($q) use ($ventaId, $user) {
                $q->where(function ($q1) use ($user) {
                    $q1->where('estado', self::PENDIENTE)->visiblesPara($user);
                });
                if ($ventaId) {
                    $q->orWhere(function ($q2) use ($ventaId) {
                        $q2->where('estado', self::APLICADA)->where('venta_id', $ventaId);
                    });
                }
            })
            ->orderBy('fecha');
    }
}
