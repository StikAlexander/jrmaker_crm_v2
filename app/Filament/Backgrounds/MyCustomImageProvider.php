<?php

namespace App\Filament\Backgrounds;

use Swis\Filament\Backgrounds\Contracts\ProvidesImages;
use Swis\Filament\Backgrounds\Image;

class MyCustomImageProvider implements ProvidesImages
{
    public static function make(): static
    {
        return new static();
    }

    public function getImage(): Image
    {
        // Verificar la URL generada por asset()
        //dd(asset('storage/images/cliente-fondo.png'));
        
        return new Image(
            asset('storage/images/cliente-fondo.png'),
            ''
        );
    }
    
}
