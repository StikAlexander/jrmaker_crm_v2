<?php

namespace App\Filament\Resources\UserCommunicationResource\Pages;

use App\Filament\Resources\UserCommunicationResource;
use App\Models\UserCommunication;  // Asegúrate de importar el modelo
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserCommunication extends CreateRecord
{
    protected static string $resource = UserCommunicationResource::class;

    /**
     * Sobrescribir el método para manejar la creación del registro
     */
    protected function handleRecordCreation(array $data): UserCommunication
    {
        // Crear el registro en la tabla 'user_communications' manualmente
        return UserCommunication::create([
            'template_id' => $data['template_id'],
            'title' => $data['title'],
            'message' => $data['message'],
        ]);
    }
}
