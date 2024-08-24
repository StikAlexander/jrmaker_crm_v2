<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use Filament\Forms\Form;
use App\Models\User;
use EightyNine\Reports\Components\Text;

class AdminsReport extends Report
{
    public ?string $heading = "Administradores Registrados";
    protected static ?string $navigationLabel = 'Administradores Reporte';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Generación de reportes';
    protected static ?int $navigationSort = 8;

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Text::make("Administradores Registrados")
                    ->title()
                    ->primary(),
                Text::make("Este reporte muestra todos los administradores registrados en el sistema.")
                    ->subtitle(),
            ]);
    }

    public function body(Body $body): Body
    {
        // Aquí mapeamos los datos de los administradores a las celdas de la tabla
        $adminRows = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->get()->map(function ($admin) {
            return Body\Layout\BodyRow::make()
                ->schema([
                    Text::make($admin->name), // Nombre del administrador
                    Text::make($admin->email), // Email del administrador
                    Text::make($admin->created_at->format('Y-m-d')), // Fecha de creación
                    Text::make($admin->status === 'active' ? 'Activo' : 'Inactivo'), // Estado del administrador
                ]);
        })->toArray(); // Convertir a un array para evitar el error de tipo

        return $body
            ->schema([
                Body\Layout\BodyColumn::make()
                    ->schema([
                        // Encabezado de la tabla
                        Body\Layout\BodyRow::make()
                            ->schema([
                                Text::make('Nombre')->title(),
                                Text::make('Email')->title(),
                                Text::make('Fecha de Creación')->title(),
                                Text::make('Estado')->title(),
                            ]),
                        // Agregar las filas de datos
                        ...$adminRows, // Descomprimir el array de filas de datos aquí
                    ]),
            ]);
    }
}
