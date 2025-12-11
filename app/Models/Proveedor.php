<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','razon','cuil','email','particular_area','particular','celular_area','celular','iva','observaciones'];


    public function getFullPhoneAttribute()
    {
        return ' Teléfono: ('.$this->particular_area.') '.$this->particular;
    }


}
