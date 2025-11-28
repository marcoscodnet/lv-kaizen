<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $fillable = [
        'sucursal_id',
        'nombre'
    ];



    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }


}
