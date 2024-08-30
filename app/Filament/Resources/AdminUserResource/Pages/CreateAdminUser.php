<?php

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\AdminUserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function afterCreate(): void
    {
        $user = $this->record;

        if ($user->exists && $user->email) {
            $user->sendVerificationEmail();

            // Mostrar la notificación de que el correo ha sido enviado
            Notification::make()
                ->title(__('Correo de verificación enviado'))
                ->success()
                ->send();
        } else {
            throw new \Exception('El usuario no se creó correctamente o falta el correo electrónico.');
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by_id'] = auth()->id();
        $user = User::create($data);
        $user->assignRole(['panel_user', 'admin']);

        return $user;
    }
}
