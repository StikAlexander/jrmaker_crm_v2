<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCommunication extends Model
{
    protected $fillable = ['template_id', 'title', 'message'];

    // Relación muchos a muchos con el modelo User
    public function clients()
    {
        return $this->belongsToMany(User::class, 'communication_user', 'communication_id', 'user_id');
    }
}
