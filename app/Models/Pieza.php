<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pieza extends Model
{
    use HasFactory;

    protected $fillable = ['codigo','descripcion','stock_minimo','costo','precio_minimo','stock_actual','observaciones','tipo_pieza_id','foto'];

    public function tipoPieza() {
        return $this->belongsTo('App\Models\TipoPieza', 'tipo_pieza_id');
    }

    public function ubicacions() {
        return $this->belongsToMany(Ubicacion::class, 'pieza_ubicacions')
            ->withTimestamps();
    }

    public function stocksPieza() {
        return $this->hasMany(StockPieza::class, 'pieza_id');
    }

    /**
     * ¿Este artículo lleva existencias? Lo define su tipo: los de tipo "Varios"
     * (patentamiento, seguro y demás conceptos) se cobran pero no se stockean.
     *
     * Un artículo sin tipo se considera con stock, que es como venía andando
     * todo antes de que existiera la tilde.
     */
    public function manejaStock(): bool
    {
        $tipo = $this->tipoPieza;

        return !$tipo || $tipo->maneja_stock;
    }

    /**
     * Ids de los artículos que NO llevan existencias.
     * Se usa para saltear el control de stock sin traer todo a memoria.
     */
    public static function idsSinStock(): array
    {
        return static::whereHas('tipoPieza', function ($q) {
            $q->where('maneja_stock', 0);
        })->pluck('id')->all();
    }
}
