<?php

namespace App\Filament\Resources\ClientUserResource\Pages;

use App\Filament\Resources\ClientUserResource;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;
use Illuminate\Validation\Rule;

class EditClientUser extends EditRecord
{
    protected static string $resource = ClientUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Impersonate::make()->record($this->getRecord()),
        ];
    }

    /**
     * Método para definir la validación del número de documento.
     */
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('document_number')
                ->label('Número de documento')
                ->required()
                ->rules([
                    'regex:/^[0-9]+$/',  // Solo números
                    Rule::unique('users', 'document_number')
                        ->ignore($this->record->id),  // Ignorar si es el mismo número del cliente editado
                ])
                ->maxLength(20)
                ->helperText('Solo se permiten números.')
                ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*'])
                ->numeric()
                ->columnSpan(1),
        ];
    }
}
