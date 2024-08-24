<?php

namespace App\Providers;

use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;

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
        // Configuración de la tabla en español
        Table::configureUsing(function (Table $table): void {
            $table
                ->emptyStateHeading('Sin datos todavía')
                ->striped()
                ->defaultPaginationPageOption(10)
                ->paginated([10, 25, 50, 100])
                ->extremePaginationLinks()
                ->defaultSort('created_at', 'desc');
        });

        // Configuración del cambio de idioma para español
        //LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
          //  $switch->locales(['es']); // Solo acepta español
        //});
    }
}