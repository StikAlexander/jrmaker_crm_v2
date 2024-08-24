<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Invoice;
use Carbon\Carbon;

class InvoicesGeneratedWidget extends ChartWidget
{
    protected static ?string $heading = 'Facturas Generadas';

    protected function getData(): array
    {
        // Obtener la cantidad de facturas generadas por mes
        $data = Invoice::query()
            ->selectRaw('MONTH(issue_date) as month_number, MONTHNAME(issue_date) as month, COUNT(*) as count')
            ->groupBy('month_number', 'month')
            ->orderBy('month_number')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Definir los meses del año para asegurar que se muestran todos, incluso si no hay datos
        $months = collect([
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ]);

        // Mapeo de datos, asegurando que cada mes tenga un valor
        $invoicesData = $months->map(function ($month) use ($data) {
            return $data[$month] ?? 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Facturas Generadas',
                    'data' => $invoicesData->toArray(),
                    'borderColor' => '#4ade80',
                    'backgroundColor' => 'rgba(74, 222, 128, 0.2)',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
