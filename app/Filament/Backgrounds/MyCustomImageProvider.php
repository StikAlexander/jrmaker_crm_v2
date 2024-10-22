<?php

namespace App\Filament\Backgrounds;

use Swis\Filament\Backgrounds\Contracts\ProvidesImages;
use Swis\Filament\Backgrounds\Image;

class MyCustomImageProvider implements ProvidesImages
{
    /**
     * Static method to create an instance of the provider.
     *
     * @return static
     */
    public static function make(): static
    {
        return new static();
    }

    /**
     * This method returns the background image.
     *
     * @return Image
     */
    public function getImage(): Image
    {
        return new Image(
            asset('storage/images/cliente-fondo.png'),  // Especifica la URL completa de la imagen
            'Atribución opcional'  // Texto de atribución opcional
        );
    }
}
