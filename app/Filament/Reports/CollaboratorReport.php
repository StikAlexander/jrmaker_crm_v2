<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Form;
use App\Models\User;
use Filament\Forms\Components\Select;
use EightyNine\Reports\Components\Body\Layout\BodyRow;
use EightyNine\Reports\Components\Body\Layout\BodyColumn;

class CollaboratorReport extends Report
{
    public ?string $heading = "Reporte de Colaboradores";
    protected static ?string $navigationGroup = 'Generación de reportes';
    protected static ?string $navigationLabel = 'Colaboradores Reporte';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make("Reporte de Colaboradores")
                                    ->title()
                                    ->primary(),
                                Text::make("Este reporte muestra todos los colaboradores registrados en el sistema.")
                                    ->subtitle(),
                            ]),
                    ]),
            ]);
    }

    public function body(Body $body): Body
    {
        $statusFilter = $this->filterData['status'] ?? null;

        $collaborators = User::whereHas('roles', function ($query) {
            $query->where('name', 'collaborator');
        })
        ->when($statusFilter, function ($query, $statusFilter) {
            return $query->where('status', $statusFilter);
        })
        ->get();

        $collaboratorRows = $collaborators->map(function ($collaborator) {
            return BodyRow::make()
                ->schema([
                    Text::make($collaborator->name), // Nombre
                    Text::make($collaborator->email), // Correo Electrónico
                    Text::make($collaborator->status === 'active' ? 'Activo' : ($collaborator->status === 'inactive' ? 'Inactivo' : 'Suspendido')), // Estado
                    Text::make($collaborator->created_at->format('Y-m-d')), // Fecha de Creación
                ]);
        });

        return $body
            ->schema([
                BodyColumn::make()
                    ->schema([
                        BodyRow::make()
                            ->schema([
                                Text::make('Nombre')->title(),
                                Text::make('Correo Electrónico')->title(),
                                Text::make('Estado')->title(),
                                Text::make('Fecha de Creación')->title(),
                            ]),
                        ...$collaboratorRows->toArray(), // Descomprimir el array de filas de colaboradores
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
            ]);
    }
}
