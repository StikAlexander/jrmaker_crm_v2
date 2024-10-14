<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserCommunicationResource\Pages;
use App\Models\User;
use App\Models\UserCommunication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserCommunicationResource extends Resource
{
    protected static ?string $model = UserCommunication::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $pluralLabel = 'Comunicados';
    protected static ?string $singularLabel = 'Comunicado';
    protected static ?string $navigationGroup = 'Actividades';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('template_id')
                    ->label('Seleccionar Plantilla de Correo')
                    ->options([
                        'solicitud_certificados' => 'Solicitud de Certificados de Retención',
                        'publicidad' => 'Correo Publicitario',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'solicitud_certificados') {
                            $set('message', 'Buen día, estimado cliente...');
                        } elseif ($state === 'publicidad') {
                            $set('message', '¡Hola! Descubre nuestras promociones especiales...');
                        } else {
                            $set('message', 'Por favor, seleccione una plantilla para ver el mensaje.');
                        }
                    }),

                Forms\Components\Textarea::make('message')
                    ->label('Mensaje del Correo')
                    ->rows(8)
                    ->required(),

                Forms\Components\CheckboxList::make('clientes')
                    ->label('Seleccionar Clientes')
                    ->options(User::whereHas('roles', function ($query) {
                        $query->where('name', 'client');
                    })->pluck('name', 'id')->toArray())
                    ->columns(2)
                    ->bulkToggleable()
                    ->required()  // Asegura que se seleccionen clientes
                    ->rules(['required', 'array', 'min:1']),  // Añade reglas de validación para forzar la selección de clientes
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('template_id')
                    ->label('Plantilla')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Mensaje')
                    ->limit(50)
                    ->sortable(),
            ])
            ->filters([])
            ->actions([])  // Se elimina la acción de "Enviar Comunicado" desde la tabla
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserCommunications::route('/'),
            'create' => Pages\CreateUserCommunication::route('/create'),
            'edit' => Pages\EditUserCommunication::route('/{record}/edit'),
        ];
    }
}
