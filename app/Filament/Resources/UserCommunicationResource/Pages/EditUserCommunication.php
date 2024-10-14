<?php

namespace App\Filament\Resources\UserCommunicationResource\Pages;

use App\Filament\Resources\UserCommunicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserCommunication extends EditRecord
{
    protected static string $resource = UserCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
