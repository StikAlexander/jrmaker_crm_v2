<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCommunication extends Model
{
    // Aquí defines los campos que se pueden asignar masivamente
    protected $fillable = [
        'template_id',  
        'title',        
        'message',      
    ];
}
