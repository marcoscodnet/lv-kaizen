<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoUnidad extends Model
{
    use HasFactory;
    protected $fillable = ['nombre'];

    public function marcas()
    {
        return $this->belongsToMany(Marca::class, 'marca_tipo_unidads');
    }
}
