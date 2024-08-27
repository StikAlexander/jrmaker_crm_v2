<?php

namespace App\Filament\Resources\ClientUserResource\Pages;

use App\Filament\Resources\ClientUserResource;
use App\Settings\MailSettings;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CreateClientUser extends CreateRecord
{
    protected static string $resource = ClientUserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $user = $this->record;
    
        // Puedes agregar cualquier otra lógica que necesites aquí
        // Por ejemplo, registrar un log de que el usuario fue creado.
        Log::info("Cliente creado:", ['user_id' => $user->id, 'email' => $user->email]);
    
        // O enviar algún tipo de notificación interna, etc.
    }
    

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by_id'] = auth()->id();
        $data['password'] = null; 
        $user = User::create($data);
        $user->assignRole(['panel_user', 'client']);

        return $user;
    }
}