<?php

namespace App\Filament\Resources\UserCommunicationResource\Pages;

use App\Filament\Resources\UserCommunicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserCommunications extends ListRecords
{
    protected static string $resource = UserCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Crear Comunicado')  
            ->icon('heroicon-o-megaphone')
            ->color('primary'),
        ];
    }
}
