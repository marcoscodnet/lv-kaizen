<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Seña de un cliente (moto, repuesto, servicio o lo que sea).
 *
 * Tiene sus propios pagos (pagos.senia_id), con cualquier forma de pago:
 * efectivo entra a la caja del día y se acredita solo; transferencia, tarjeta,
 * etc. van a Auditoría. La seña recién se puede usar cuando todos sus pagos que
 * requieren autorización están autorizados, y vale lo ACREDITADO.
 *
 * Estados guardados: Vigente (todavía no se usó), Aplicada (es pago de una
 * venta / venta de artículos / servicio), Devuelta o Retenida. Sobre una
 * Vigente se calcula si está "Pendiente de autorización" o "Disponible".
 */
class Senia extends Model
{
    public const VIGENTE   = 'Vigente';
    public const APLICADA  = 'Aplicada';
    public const DEVUELTA  = 'Devuelta';
    public const RETENIDA  = 'Retenida';

    // Estados que se muestran (lo calculado sobre una Vigente)
    public const PENDIENTE_AUTORIZACION = 'Pendiente de autorización';
    public const DISPONIBLE             = 'Disponible';

    public const CONCEPTO            = 'Seña';
    public const CONCEPTO_DEVOLUCION = 'Devolución de seña';
    public const ENTIDAD             = 'Seña';

    /** Columna de pagos / senias según dónde se aplica. */
    public const DESTINOS = ['venta_id', 'venta_pieza_id', 'servicio_id'];

    protected $fillable = [
        'cliente_id', 'sucursal_id', 'fecha', 'monto', 'detalle', 'estado',
        'venta_id', 'venta_pieza_id', 'servicio_id', 'pago_aplicado_id',
        'fecha_baja', 'devolucion_entidad_id', 'devolucion_monto', 'observacion', 'user_id',
    ];

    protected $casts = [
        'fecha'      => 'datetime',
        'fecha_baja' => 'datetime',
    ];

    /* ---------- Relaciones ---------- */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Pagos con los que el cliente dejó la seña. */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'senia_id');
    }

    /** Pago de la operación con el que se aplicó. */
    public function pagoAplicado()
    {
        return $this->belongsTo(Pago::class, 'pago_aplicado_id');
    }

    public function movimientosCaja()
    {
        return $this->hasMany(MovimientoCaja::class, 'senia_id');
    }

    public function movimientosCuenta()
    {
        return $this->hasMany(MovimientoCuenta::class, 'senia_id');
    }

    public function devolucionEntidad()
    {
        return $this->belongsTo(Entidad::class, 'devolucion_entidad_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function ventaPieza()
    {
        return $this->belongsTo(VentaPieza::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    /* ---------- Estado ---------- */

    public function esVigente(): bool
    {
        return $this->estado === self::VIGENTE;
    }

    /**
     * Pagos que requieren autorización y todavía no la tienen.
     * Usa las relaciones cargadas si están (pagos.entidad, pagos.autorizacion).
     */
    public function pagosSinAutorizar()
    {
        return $this->pagos->filter(function (Pago $p) {
            return $p->entidad && $p->entidad->autorizacion && !$p->autorizacion;
        });
    }

    public function estaAutorizada(): bool
    {
        return $this->pagosSinAutorizar()->isEmpty();
    }

    /** Lo que efectivamente entró: suma de lo acreditado de sus pagos. */
    public function acreditado(): float
    {
        return round((float) $this->pagos->sum(function (Pago $p) {
            return (float) $p->pagado;
        }), 2);
    }

    /** Se puede aplicar: vigente, todo autorizado y con algo acreditado. */
    public function estaDisponible(): bool
    {
        return $this->esVigente() && $this->estaAutorizada() && $this->acreditado() > 0;
    }

    /** Estado para mostrar en pantalla. */
    public function estadoVisible(): string
    {
        if (!$this->esVigente()) {
            return $this->estado;
        }
        return $this->estaAutorizada() ? self::DISPONIBLE : self::PENDIENTE_AUTORIZACION;
    }

    public function colorEstado(): string
    {
        return [
            self::PENDIENTE_AUTORIZACION => 'warning',
            self::DISPONIBLE             => 'primary',
            self::APLICADA               => 'success',
            self::DEVUELTA               => 'secondary',
            self::RETENIDA               => 'info',
        ][$this->estadoVisible()] ?? 'secondary';
    }

    /** "venta #12", "venta de artículos #5", "servicio #8" y su ruta. */
    public function aplicadaA(): ?array
    {
        if ($this->venta_id) {
            return ['texto' => 'venta #' . $this->venta_id, 'url' => route('ventas.show', $this->venta_id)];
        }
        if ($this->venta_pieza_id) {
            return ['texto' => 'venta de artículos #' . $this->venta_pieza_id, 'url' => route('ventaPiezas.show', $this->venta_pieza_id)];
        }
        if ($this->servicio_id) {
            return ['texto' => 'servicio #' . $this->servicio_id, 'url' => route('servicios.show', $this->servicio_id)];
        }
        return null;
    }

    /* ---------- Catálogos que usa el sistema ---------- */

    public static function concepto(): Concepto
    {
        return Concepto::firstOrCreate(
            ['nombre' => self::CONCEPTO],
            ['tipo' => 'Suma', 'activo' => 0]
        );
    }

    public static function conceptoDevolucion(): Concepto
    {
        return Concepto::firstOrCreate(
            ['nombre' => self::CONCEPTO_DEVOLUCION],
            ['tipo' => 'Resta', 'activo' => 0]
        );
    }

    /** Forma de pago con la que la seña entra a la operación. */
    public static function entidad(): Entidad
    {
        return Entidad::firstOrCreate(
            ['nombre' => self::ENTIDAD],
            ['forma' => 'Contado', 'autorizacion' => 0, 'tangible' => 0, 'cuenta' => 0, 'activa' => 0]
        );
    }

    /* ---------- Sucursales ---------- */

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
     * Señas del cliente para mostrar al cobrar una operación: las vigentes
     * (disponibles o pendientes de autorización, estas últimas no se pueden
     * tildar) y, al editar, las que ya están aplicadas a esa misma operación.
     *
     * @param string   $columna  venta_id | venta_pieza_id | servicio_id
     * @param int|null $id       id de la operación al editar
     */
    public static function delClienteParaCobrar($clienteId, string $columna, $id, $user)
    {
        // La restricción por sucursal vale para elegir señas vigentes; las que
        // ya están aplicadas a esta operación se muestran siempre, si no un
        // usuario de otra sucursal las liberaría sin querer al editar.
        return static::with('pagos.entidad', 'pagos.autorizacion', 'sucursal')
            ->where('cliente_id', $clienteId)
            ->where(function ($q) use ($columna, $id, $user) {
                $q->where(function ($q1) use ($user) {
                    $q1->where('estado', self::VIGENTE)->visiblesPara($user);
                });
                if ($id) {
                    $q->orWhere(function ($q2) use ($columna, $id) {
                        $q2->where('estado', self::APLICADA)->where($columna, $id);
                    });
                }
            })
            ->orderBy('fecha');
    }
}
