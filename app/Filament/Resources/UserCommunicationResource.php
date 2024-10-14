<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserCommunicationResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCommunicationMail;  // El Mailable para el envío de correos
use Filament\Notifications\Notification;

class UserCommunicationResource extends Resource
{
    // No necesitamos definir un modelo si no estamos guardando los datos en la base de datos
    // protected static ?string $model = UserCommunication::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Selección de plantilla de correo
                Forms\Components\Select::make('template_id')
                    ->label('Seleccionar Plantilla de Correo')
                    ->options([
                        'solicitud_certificados' => 'Solicitud de Certificados de Retención',
                        'publicidad' => 'Correo Publicitario',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('preview', self::getPreview($state))),  // Actualiza la vista previa según la plantilla seleccionada

                // Vista previa del correo seleccionado
                Forms\Components\Textarea::make('preview')
                    ->label('Vista Previa del Correo')
                    ->disabled()
                    ->rows(8),

                // Selección de usuarios (clientes)
                Forms\Components\CheckboxList::make('clientes')
                    ->label('Seleccionar Clientes')
                    ->relationship('client', 'name')  // Ajusta la relación según tu estructura
                    ->options(User::whereHas('roles', function ($query) {
                        $query->where('name', 'client');
                    })->pluck('name', 'id'))
                    ->columns(2)  // Se adapta a pantallas grandes
                    ->bulkToggleable(), // Permite seleccionar todos los clientes a la vez
            ]);
    }

    // Método para obtener la vista previa de la plantilla seleccionada
    public static function getPreview($templateId)
    {
        switch ($templateId) {
            case 'solicitud_certificados':
                return 'Estimado cliente, le solicitamos por favor que nos haga llegar su certificado de retención correspondiente.';
            case 'publicidad':
                return '¡Hola! Descubre nuestras promociones especiales para fin de año...';
            default:
                return 'Por favor, seleccione una plantilla para ver la vista previa.';
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Puedes agregar columnas si decides almacenar las comunicaciones
            ])
            ->filters([
                // Filtros si son necesarios
            ])
            ->actions([
                // Acción personalizada para enviar correos
                Tables\Actions\Action::make('enviar_comunicado')
                    ->label('Enviar Comunicado')
                    ->action(function (array $data) {
                        // Obtiene los clientes seleccionados
                        $clientes = User::whereIn('id', $data['clientes'])->get();

                        // Enviar el correo a cada cliente seleccionado
                        foreach ($clientes as $cliente) {
                            Mail::to($cliente->email)
                                ->send(new UserCommunicationMail($cliente, $data['template_id']));
                        }

                        // Mensaje de éxito después de enviar los correos
                        Notification::make()
                            ->title('Comunicado Enviado')
                            ->body('El comunicado ha sido enviado exitosamente a los clientes seleccionados.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()  // Pide confirmación antes de enviar
                    ->color('primary')
                    ->icon('heroicon-o-paper-airplane'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),  // Acción de eliminación masiva
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define relaciones si es necesario
        ];
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
