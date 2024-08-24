<?php

namespace App\Models;


class ClientUser extends User
{
    // protected $table = 'users';

    // protected $guard_name = 'web';
    protected static function booted()
    {
        static::creating(function ($user) {
            $user->password = null; // No se asigna contraseña
        });
    }
}

