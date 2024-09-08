<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DashboardSummaryWidget extends BaseWidget
{
    protected static ?string $heading = 'Resumen del Negocio';

    protected function getCards(): array
    {
        return [
            Card::make('Ingresos Totales', '$' . number_format(Invoice::sum('total_paid'), 0))
                ->description('Suma total de los ingresos recibidos en el sistema')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('success'),

            Card::make('Total Facturas Pendientes', '$' . number_format(Invoice::where('status', 'Pending')->sum('pending_amount'), 0))
                ->description('Saldo pendiente de todas las facturas')
                ->descriptionIcon('heroicon-s-exclamation-circle')
                ->color('warning'),

            
            Card::make('Soportes de pago aprobados', number_format(Payment::where('payment_status', 'Completed')->count() / Payment::count() * 100, 0) . '%')
                ->description('Porcentaje de Payments completados')
                ->descriptionIcon('heroicon-s-check-circle')
                ->color('success'),

            Card::make('Soportes de pago pendientes', number_format(Payment::where('payment_status', 'Pending')->count() / Payment::count() * 100, 0) . '%')
                ->description('Porcentaje de Payments pendientes')
                ->descriptionIcon('heroicon-s-exclamation-circle')
                ->color('warning'),
        ];
    }
}
