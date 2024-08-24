<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Form;
use App\Models\User;
use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use EightyNine\Reports\Components\Body\Layout\BodyRow;
use EightyNine\Reports\Components\Body\Layout\BodyColumn;

class ClientReport extends Report
{
    public ?string $heading = "Reporte de Clientes";
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Clientes Reporte';
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
                                Text::make("Reporte de Clientes")
                                    ->title()
                                    ->primary(),
                                Text::make("Este reporte muestra todos los clientes registrados en el sistema, junto con un resumen de sus facturas.")
                                    ->subtitle(),
                            ]),
                    ]),
            ]);
    }

    public function body(Body $body): Body
    {
        $statusFilter = $this->filterData['status'] ?? null;
        $startDate = $this->filterData['start_date'] ?? now()->startOfYear();
        $endDate = $this->filterData['end_date'] ?? now()->endOfYear();

        // Filtrar clientes por estado y fecha de registro
        $clients = User::whereHas('roles', function ($query) {
            $query->where('name', 'client');
        })
        ->when($statusFilter, function ($query, $statusFilter) {
            return $query->where('status', $statusFilter);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

        // Obtener el conteo de facturas para cada cliente
        $clientsWithInvoiceCount = $clients->map(function ($client) {
            $invoiceCount = Invoice::where('client_id', $client->id)->count();

            return [
                'document_number' => $client->document_number,
                'name' => $client->name,
                'email' => $client->email,
                'status' => $client->status,
                'created_at' => $client->created_at->format('Y-m-d'),
                'invoice_count' => $invoiceCount,
            ];
        });

        // Crear filas de datos para cada cliente
        $clientRows = $clientsWithInvoiceCount->map(function ($client) {
            return BodyRow::make()
                ->schema([
                    Text::make($client['document_number']), // Número de identificación
                    Text::make($client['name']), // Nombre
                    Text::make($client['email']), // Correo Electrónico
                    Text::make($client['status'] === 'active' ? 'Activo' : ($client['status'] === 'inactive' ? 'Inactivo' : 'Suspendido')), // Estado
                    Text::make($client['created_at']), // Fecha de Creación
                    Text::make($client['invoice_count']), // Cantidad de Facturas
                ]);
        });

        return $body
            ->schema([
                BodyColumn::make()
                    ->schema([
                        BodyRow::make()
                            ->schema([
                                Text::make('Número de Identificación')->title(),
                                Text::make('Nombre')->title(),
                                Text::make('Correo Electrónico')->title(),
                                Text::make('Estado')->title(),
                                Text::make('Fecha de Creación')->title(),
                                Text::make('Facturas Asociadas')->title(),
                            ]),
                        ...$clientRows->toArray(), // Descomprimir el array de filas de clientes
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
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'suspended' => 'Suspendido',
                    ])
                    ->placeholder('Todos los estados'),
                DatePicker::make('start_date')
                    ->label('Fecha de inicio')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Fecha de fin')
                    ->required(),
            ]);
    }
}
