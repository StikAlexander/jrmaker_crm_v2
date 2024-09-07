<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configuración global de la tabla en Filament
        Table::configureUsing(function (Table $table): void {
            $table
                ->emptyStateHeading('Sin datos todavía')
                ->striped()
                ->defaultPaginationPageOption(10)
                ->paginated([10, 25, 50, 100])
                ->extremePaginationLinks()
                ->defaultSort('created_at', 'desc');
        });

        // Registro del archivo JS personalizado en Filament
        FilamentAsset::register([
            Js::make('custom-script', asset('js/custom.js')),
        ]);
    }
}
