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
        // Aquí especificamos la imagen que ya tienes en el directorio
        return new Image(
            asset('storage/images/cliente-fondo.png'),  // Ruta a la imagen 'cliente-fondo.png'
            ''  // Deja en blanco o añade texto si necesitas atribución
        );
    }
}
