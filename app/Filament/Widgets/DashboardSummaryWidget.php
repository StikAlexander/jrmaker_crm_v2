<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\VoucherPayment;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DashboardSummaryWidget extends BaseWidget
{
    protected static ?string $heading = 'Resumen del Negocio';

    protected function getCards(): array
    {
        return [
            Card::make('Ingresos Totales', number_format(Invoice::sum('total_paid'), 2))
                ->description('Suma total de los ingresos recibidos en el sistema')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('success'),

            /*Card::make('Total Facturas Pagadas', number_format(Invoice::where('status', 'Paid')->sum('total_amount'), 2))
                ->description('Total de facturas pagadas en el sistema')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('success'),*/

            Card::make('Total Facturas Pendientes', number_format(Invoice::where('status', 'Pending')->sum('pending_amount'), 2))
                ->description('Saldo pendiente de todas las facturas')
                ->descriptionIcon('heroicon-s-exclamation-circle')
                ->color('warning'),

            Card::make('Soportes de pago aprobados', number_format(VoucherPayment::where('confirmation_status', 'Approved')->count() / VoucherPayment::count() * 100, 2) . '%')
                ->description('Porcentaje de vouchers aprobados')
                ->descriptionIcon('heroicon-s-check-circle')
                ->color('success'),

        Card::make('Soportes de pago Pendientes', number_format(VoucherPayment::where('confirmation_status', 'Pending')->count() / VoucherPayment::count() * 100, 2) . '%')
                ->description('Porcentaje de vouchers pendientes')
                ->descriptionIcon('heroicon-s-exclamation-circle')
                ->color('warning'),
        ];
    }
}

