<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pieza extends Model
{
    use HasFactory;

    protected $fillable = ['codigo','descripcion','stock_minimo','costo','precio_minimo','stock_actual','observaciones','tipo_pieza_id'];

    public function tipoPieza() {
        return $this->belongsTo('App\Models\TipoPieza', 'tipo_pieza_id');
    }

    public function stocksPieza() {
        return $this->hasMany(StockPieza::class, 'pieza_id');
    }
}
