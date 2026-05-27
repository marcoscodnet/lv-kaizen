<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autorizacion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'user_name', 'pago_id', 'fecha', 'observaciones'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    // Authorization belongs to a single payment
    public function pago()
    {
        return $this->belongsTo('App\Models\Pago', 'pago_id');
    }
}
