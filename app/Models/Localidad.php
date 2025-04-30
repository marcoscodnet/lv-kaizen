<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','provincia_id'];
    public function provincia()
    {
        return $this->belongsTo('App\Models\Provincia');
    }

    public function clientes()
    {
        return $this->hasMany('App\Models\Cliente');
    }

    public function sucursals()
    {
        return $this->hasMany('App\Models\Sucursal');
    }

}
