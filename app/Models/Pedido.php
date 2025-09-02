<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = ['pieza_id','nombre','cantidad','senia','minimo','fecha','estado','observacion'];



    public function pieza() {
        return $this->belongsTo('App\Models\Pieza', 'pieza_id');
    }


}
