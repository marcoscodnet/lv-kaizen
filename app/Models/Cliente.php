<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','documento','cuil','estado_civil','nacimiento','email','particular_area','particular','celular_area','celular','calle','nro','piso','depto','localidad_id','cp','nacionalidad','ocupacion','trabajo','iva','llego','foto','observaciones'];


    public function localidad()
    {
        return $this->belongsTo('App\Models\Localidad');
    }

    public function getFullNamePhoneAttribute()
    {
        return $this->nombre.' '.' ('.$this->cuil.')';
    }

}
