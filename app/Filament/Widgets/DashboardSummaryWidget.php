<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class DashboardSummaryWidget extends BaseWidget
{
    protected static ?string $heading = 'Resumen del Negocio';

    protected function getCards(): array
    {
        // Optimización: Una sola consulta para todos los conteos de pagos por estado
        $paymentsCountByStatus = DB::table('payments')
            ->select('payment_status', DB::raw('count(*) as total'))
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status')
            ->toArray();
            
        $totalPayments = array_sum($paymentsCountByStatus);
        $successfulPayments = $paymentsCountByStatus['Completed'] ?? 0;
        $failedPayments = 0;
        
        // Sumar todos los estados fallidos
        foreach (['Failed', 'Declined', 'Error', 'Cancelled'] as $status) {
            $failedPayments += $paymentsCountByStatus[$status] ?? 0;
        }
        
        // Optimización: Consulta única para obtener sumas de facturas
        $invoiceSummary = DB::table('invoices')
            ->select(
                DB::raw('SUM(total_paid) as total_paid'),
                DB::raw('SUM(CASE WHEN status = "Pending" THEN pending_amount ELSE 0 END) as total_pending')
            )
            ->first();

        return [
            // Ingresos Totales con consulta optimizada
            Card::make('Ingresos Totales', '$' . number_format($invoiceSummary->total_paid ?? 0, 0))
                ->description('Suma total de los ingresos recibidos en el sistema')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('success'),

            // Ingresos Pendientes con consulta optimizada
            Card::make('Ingresos Pendientes', '$' . number_format($invoiceSummary->total_pending ?? 0, 0))
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
