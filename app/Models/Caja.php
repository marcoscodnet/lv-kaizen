<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'user_id',
        'apertura',
        'cierre',
        'inicial',
        'final',
        'estado',
    ];

    protected $dates = ['apertura', 'cierre']; // <-- esto convierte automáticamente a Carbon

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
