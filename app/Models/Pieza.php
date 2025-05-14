<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pieza extends Model
{
    use HasFactory;

    protected $fillable = ['codigo','descripcion','stock_minimo','costo','precio_minimo','stock_actual','observaciones'];

}
