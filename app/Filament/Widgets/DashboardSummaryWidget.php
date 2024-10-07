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
        $totalPayments = Payment::count();
        $successfulPayments = Payment::where('payment_status', 'Completed')->count();
        $failedPayments = Payment::whereIn('payment_status', ['Failed', 'Declined', 'Error', 'Cancelled'])->count();
        $pendingPayments = Payment::where('payment_status', 'Pending')->count();

        return [
            // Ingresos Totales (sin cambios)
            Card::make('Ingresos Totales', '$' . number_format(Invoice::sum('total_paid'), 0))
                ->description('Suma total de los ingresos recibidos en el sistema')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('success'),

            // Nuevo: Ingresos Pendientes
            Card::make('Ingresos Pendientes', '$' . number_format(Invoice::where('status', 'Pending')->sum('pending_amount'), 0))
                ->description('Saldo pendiente de todas las facturas')
                ->descriptionIcon('heroicon-s-exclamation-circle')
                ->color('warning'),

            // Porcentaje de Pagos Exitosos
            Card::make('Pagos Exitosos', $totalPayments > 0 ? number_format(($successfulPayments / $totalPayments) * 100, 0) . '%' : '0%')
                ->description('Porcentaje de pagos exitosos')
                ->descriptionIcon('heroicon-s-check-circle')
                ->color('success'),

            // Porcentaje de Pagos Fallidos
            Card::make('Pagos Fallidos', $totalPayments > 0 ? number_format(($failedPayments / $totalPayments) * 100, 0) . '%' : '0%')
                ->description('Porcentaje de pagos fallidos')
                ->descriptionIcon('heroicon-s-x-circle')
                ->color('danger'),
        ];
    }
}
