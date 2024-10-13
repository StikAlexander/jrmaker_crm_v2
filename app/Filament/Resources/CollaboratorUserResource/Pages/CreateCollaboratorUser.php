<?php

namespace App\Filament\Resources\CollaboratorUserResource\Pages;

use App\Filament\Resources\CollaboratorUserResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCollaboratorUser extends CreateRecord
{
    protected static string $resource = CollaboratorUserResource::class;

    public function getTitle(): string
    {
        return 'Crear Colaborador';
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if ($user->exists && $user->email) {
            $user->sendVerificationEmail();

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
        $user->assignRole(['panel_user', 'collaborator']);

        return $user;
    }
}
