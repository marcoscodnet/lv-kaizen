<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autorizacion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','user_name','unidad_id','fecha'];


    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }


    public function unidad() {
        return $this->belongsTo('App\Models\Unidad', 'unidad_id');
    }
}
