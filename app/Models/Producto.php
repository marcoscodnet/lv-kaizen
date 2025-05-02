<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = ['tipo_unidad_id','marca_id','modelo_id','color_id','precio','minimo','discontinuo'];

    public function tipoUnidad() {
        return $this->belongsTo('App\Models\TipoUnidad', 'tipo_unidad_id');
    }

    public function marca() {
        return $this->belongsTo('App\Models\Marca', 'marca_id');
    }

    public function color() {
        return $this->belongsTo('App\Models\Color', 'color_id');
    }

    public function modelo() {
        return $this->belongsTo('App\Models\Modelo', 'modelo_id');
    }
}
