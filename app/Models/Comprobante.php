<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $fillable = ['pago_id', 'path'];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }
}
