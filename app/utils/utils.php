<?php

namespace App\Utils;

class Utils
{
    public static function isResourceNavigationGroupEnabled(): bool
    {
        return config('filament-shield.shield_resource.navigation_group') === 'settings';
    }
}
