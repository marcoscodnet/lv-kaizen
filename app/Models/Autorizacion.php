<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autorizacion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'user_name', 'autorizable_id', 'autorizable_type', 'fecha'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    // Polymorphic: points to Venta, VentaPieza or Servicio
    public function autorizable()
    {
        return $this->morphTo();
    }
}
