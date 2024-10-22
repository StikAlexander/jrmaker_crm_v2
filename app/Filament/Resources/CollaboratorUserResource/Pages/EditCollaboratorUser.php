<?php

namespace App\Filament\Resources\CollaboratorUserResource\Pages;

use App\Filament\Resources\CollaboratorUserResource;
use App\Notifications\CustomVerifyEmail;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\Rule;
use Filament\Forms\Components\TextInput;

class EditCollaboratorUser extends EditRecord
{
    protected static string $resource = CollaboratorUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function resendVerificationEmail(): void
    {
        $user = $this->record;

        $notification = new CustomVerifyEmail(Filament::getVerifyEmailUrl($user));

        $user->notify($notification);

        Notification::make()
            ->title(__('resource.user.notifications.notification_resent.title'))
            ->success()
            ->send();
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('document_number')
                ->label('Número de documento')
                ->required()
                ->rules([
                    'regex:/^[0-9]+$/',
                    Rule::unique('users', 'document_number')
                        ->ignore($this->record->id),
                ])
                ->maxLength(20)
                ->helperText('Solo se permiten números.')
                ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*'])
                ->numeric()
                ->columnSpan(1),
        ];
    }
}
