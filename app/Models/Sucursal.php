<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;
    protected $fillable = ['nombre','email','telefono','direccion','localidad_id','comentario','activa'];

    public function localidad()
    {
        return $this->belongsTo('App\Models\Localidad');
    }
}
