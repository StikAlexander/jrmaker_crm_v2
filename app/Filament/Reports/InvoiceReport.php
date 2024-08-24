<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Form;
use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use EightyNine\Reports\Components\Body\Layout\BodyRow;
use EightyNine\Reports\Components\Body\Layout\BodyColumn;

class InvoiceReport extends Report
{
    public ?string $heading = "Reporte de Facturas Generadas";
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Facturas Reporte';
    protected static ?string $navigationGroup = 'Generación de reportes';
    protected static ?int $navigationSort = 7;

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make("Reporte de Facturas Generadas")
                                    ->title()
                                    ->primary(),
                                Text::make("Este reporte muestra todas las facturas generadas dentro del rango de fechas seleccionado.")
                                    ->subtitle(),
                            ]),
                    ]),
            ]);
    }

    public function body(Body $body): Body
    {
        $startDate = $this->filterData['start_date'] ?? now()->startOfYear();
        $endDate = $this->filterData['end_date'] ?? now()->endOfYear();

        $invoices = Invoice::whereBetween('issue_date', [$startDate, $endDate])->get();

        $invoiceRows = $invoices->map(function ($invoice) {
            return BodyRow::make()
                ->schema([
                    Text::make($invoice->invoice_number), // Número de factura
                    Text::make($invoice->client->name), // Cliente
                    Text::make($invoice->issue_date->format('Y-m-d')), // Fecha de emisión
                    Text::make($invoice->due_date->format('Y-m-d')), // Fecha de vencimiento
                    Text::make(number_format($invoice->total_amount, 2)), // Monto total
                    Text::make(number_format($invoice->total_paid, 2)), // Monto pagado
                    Text::make(number_format($invoice->pending_amount, 2)), // Monto pendiente
                    Text::make($invoice->status === 'Paid' ? 'Pagada' : 'Pendiente'), // Estado
                ]);
        });

        return $body
            ->schema([
                BodyColumn::make()
                    ->schema([
                        BodyRow::make()
                            ->schema([
                                Text::make('Número de Factura')->title(),
                                Text::make('Cliente')->title(),
                                Text::make('Fecha de Emisión')->title(),
                                Text::make('Fecha de Vencimiento')->title(),
                                Text::make('Monto Total')->title(),
                                Text::make('Monto Pagado')->title(),
                                Text::make('Monto Pendiente')->title(),
                                Text::make('Estado')->title(),
                            ]),
                        ...$invoiceRows->toArray(), // Descomprimir el array de filas de facturas
                    ]),
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([
                Footer\Layout\FooterRow::make()
                    ->schema([
                        Footer\Layout\FooterColumn::make()
                            ->schema([
                                Text::make("Reporte generado el " . now()->format('Y-m-d H:i:s')),
                            ])
                            ->alignRight(),
                    ]),
            ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->label('Fecha de inicio')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Fecha de fin')
                    ->required(),
            ]);
    }
}
