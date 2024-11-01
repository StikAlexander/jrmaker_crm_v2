<?php

namespace App\Filament\Backgrounds;

use Illuminate\Support\Facades\Log;
use Swis\Filament\Backgrounds\Contracts\ProvidesImages;
use Swis\Filament\Backgrounds\Image;

class MyImageProvider implements ProvidesImages
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getImage(): Image
    {
        Log::info('MyImageProvider llamado'); 
        return new Image(
            //'url("https://crm.jrmaker.com.co/images/cliente-fondo/pagos-jr.jpg")', 
            //'url("/images/cliente-fondo/pagos-jr.jpg")',
            'url("/images/cliente-fondo/pagos-jr.webp")',
            '' 
        );
    }
    
}
