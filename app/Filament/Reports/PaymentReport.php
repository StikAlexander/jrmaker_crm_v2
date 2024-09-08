<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use Filament\Forms\Form;
use EightyNine\Reports\Components\Text;
use App\Models\Payment;
use Filament\Forms\Components\DatePicker;
use EightyNine\Reports\Components\Body\Layout\BodyRow;
use EightyNine\Reports\Components\Body\Layout\BodyColumn;
use Illuminate\Support\Carbon;

class PaymentReport extends Report
{
    public ?string $heading = "Reporte de Pagos de Payment";
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Soportes de Pago Reporte';
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
                                Text::make("Reporte de Pagos de Payment")
                                    ->title()
                                    ->primary(),
                                Text::make("Este reporte muestra todos los pagos de Payment realizados dentro del rango de fechas seleccionado.")
                                    ->subtitle(),
                            ]),
                    ]),
            ]);
    }

    public function body(Body $body): Body
    {
        $startDate = $this->filterData['start_date'] ?? now()->startOfYear();
        $endDate = $this->filterData['end_date'] ?? now()->endOfYear();

        $Payments = Payment::whereBetween('payment_date', [$startDate, $endDate])->get();

        $PaymentRows = $Payments->map(function ($Payment) {
            return BodyRow::make()
                ->schema([
                    Text::make($Payment->Payment_number), // Número de Payment
                    Text::make($Payment->client->name), // Cliente
                    Text::make(Carbon::parse($Payment->payment_date)->format('Y-m-d')), // Fecha de pago
                    Text::make(number_format($Payment->amount, 2)), // Monto
                    Text::make($Payment->confirmation_status === 'Approved' ? 'Aprobado' : ($Payment->confirmation_status === 'Pending' ? 'Pendiente' : 'Rechazado')), // Estado
                ]);
        });

        return $body
            ->schema([
                BodyColumn::make()
                    ->schema([
                        BodyRow::make()
                            ->schema([
                                Text::make('Número de Payment')->title(),
                                Text::make('Cliente')->title(),
                                Text::make('Fecha de Pago')->title(),
                                Text::make('Monto')->title(),
                                Text::make('Estado')->title(),
                            ]),
                        ...$PaymentRows->toArray(), 
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