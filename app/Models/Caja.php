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
     * Cajas vencidas: quedaron 'Abierta' y su día ya pasó.
     *
     * Excepción: una caja vieja que un administrador reabrió HOY (updated_at de
     * hoy) no está vencida hasta la medianoche, para que pueda corregirla y
     * cerrarla. Cargar movimientos no toca updated_at de la caja, así que esto
     * solo lo dispara el reabrir.
     */
    public function scopeVencidas($query)
    {
        $hoy = now()->startOfDay();

        return $query->where('estado', 'Abierta')
            ->whereDate('fecha', '<', $hoy->toDateString())
            ->where(function ($q) use ($hoy) {
                $q->whereNull('updated_at')->orWhere('updated_at', '<', $hoy);
            });
    }

    public function estaVencida(): bool
    {
        return static::vencidas()->whereKey($this->getKey())->exists();
    }

    /**
     * Cierra todas las cajas vencidas. Lo usa el comando de medianoche y,
     * como red de seguridad si el cron no corrió, cada pantalla de caja y de
     * movimientos antes de mostrar o guardar nada.
     *
     * El cierre queda fechado al final del día de la caja, no al momento en
     * que se detectó: así el arqueo y los listados muestran el día correcto.
     *
     * @return \Illuminate\Support\Collection  Cajas cerradas
     */
    public static function cerrarVencidas($sucursalId = null)
    {
        $cajas = static::vencidas()
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            })
            ->orderBy('fecha')
            ->get();

        foreach ($cajas as $caja) {
            $caja->cerrar($caja->fecha->copy()->endOfDay());
            \Log::info("Cierre automático de caja #{$caja->id} del {$caja->fecha->format('d/m/Y')}", [
                'sucursal_id' => $caja->sucursal_id,
                'final' => $caja->final,
            ]);
        }

        return $cajas;
    }

    /**
     * Cierra la caja calculando el monto final.
     *
     * final = efectivo total en la caja al cerrar: ingresos acreditados (incluye
     * el fondo inicial, registrado como movimiento de "Apertura") menos egresos.
     * Lo usan el cierre manual (CajaController@cerrar) y el automático
     * (comando cajas:cerrar-vencidas), para que ambos hagan la misma cuenta.
     */
    public function cerrar($momento = null): self
    {
        $ingresosAcreditados = $this->movimientos()
            ->where('tipo', 'Ingreso')->where('acreditado', true)->sum('monto');
        $egresos = $this->movimientos()->where('tipo', 'Egreso')->sum('monto');

        $this->final = $ingresosAcreditados - $egresos;
        $this->cierre = $momento ?: now();
        $this->estado = 'Cerrada';
        $this->save();

        return $this;
    }
}
