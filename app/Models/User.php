<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'sucursal_id', // Nuevo campo facultad_id
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function getRoleNames()
    {
        return $this->belongsToMany(Role::class);

    }

    /**
     * Define la relación con la facultad.
     *
     * @return BelongsTo
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Ve el detalle de las operaciones de otros vendedores: el Administrador
     * y quien tenga el permiso `operacion-ver-todas`.
     */
    public function veOperacionesAjenas(): bool
    {
        return $this->hasRole('Administrador') || $this->can('operacion-ver-todas');
    }

    /**
     * Cada vendedor ve (y edita) solo sus operaciones: venta, venta de
     * artículos. La operación es "suya" si figura como su vendedor (user_id).
     */
    public function puedeVerOperacion($operacion): bool
    {
        return $operacion
            && ($this->veOperacionesAjenas() || (int) $operacion->user_id === (int) $this->id);
    }
}
