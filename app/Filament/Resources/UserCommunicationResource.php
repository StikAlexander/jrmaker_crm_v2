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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título del Correo')
                    ->required(),

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
                            $set('message', 'Estimado cliente, le solicitamos por favor que nos haga llegar su certificado de retención correspondiente.');
                        } elseif ($state === 'publicidad') {
                            $set('message', '¡Hola! Descubre nuestras promociones especiales para fin de año...');
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
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Mensaje')
                    ->limit(50)
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                // Acción comentada para enviar comunicado desde la tabla
                // Tables\Actions\Action::make('enviar_comunicado')
                //     ->label('Enviar Comunicado')
                //     ->action(function (array $data) {
                //         try {
                //             if (!isset($data['clientes']) || empty($data['clientes'])) {
                //                 throw new \Exception('No se seleccionaron clientes para enviar el comunicado.');
                //             }
                //
                //             $clientes = User::whereIn('id', $data['clientes'])->get();
                //
                //             foreach ($clientes as $cliente) {
                //                 Mail::to($cliente->email)
                //                     ->send(new UserCommunicationMail($cliente, $data['template_id']));
                //             }
                //
                //             Notification::make()
                //                 ->title('Comunicado Enviado')
                //                 ->body('El comunicado ha sido enviado exitosamente a los clientes seleccionados.')
                //                 ->success()
                //                 ->send();
                //         } catch (\Exception $e) {
                //             Notification::make()
                //                 ->title('Error')
                //                 ->body('Ocurrió un error al intentar enviar el comunicado: ' . $e->getMessage())
                //                 ->danger()
                //                 ->send();
                //         }
                //     })
                //     ->requiresConfirmation()
                //     ->color('primary')
                //     ->icon('heroicon-o-paper-airplane'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
