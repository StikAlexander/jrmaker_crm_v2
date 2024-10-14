<?php

namespace App\Filament\Resources\UserCommunicationResource\Pages;

use App\Filament\Resources\UserCommunicationResource;
use App\Models\User;
use App\Mail\UserCommunicationMail;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class CreateUserCommunication extends CreateRecord
{
    protected static string $resource = UserCommunicationResource::class;

    public function getTitle(): string
    {
        return 'Crear Comunicado';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            // Verifica si se seleccionaron clientes
            if (!isset($data['clientes']) || empty($data['clientes'])) {
                throw new \Exception('No se seleccionaron clientes para enviar el comunicado.');
            }

            // Obtener los clientes seleccionados
            $clientes = User::whereIn('id', $data['clientes'])->get();

            // Enviar el correo a cada cliente
            foreach ($clientes as $cliente) {
                Mail::to($cliente->email)
                    ->send(new UserCommunicationMail($cliente, $data['template_id']));
            }

            // Notificación de éxito
            Notification::make()
                ->title('Comunicado Enviado')
                ->body('El comunicado ha sido enviado exitosamente a los clientes seleccionados.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Registro del error en los logs
            Log::error('Error al enviar el comunicado: ' . $e->getMessage());

            // Notificación de error en la UI
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al intentar enviar el comunicado: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        return $data;
    }
}
