<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as AuthLogin;
use Coderflex\FilamentTurnstile\Forms\Components\Turnstile; 
use Illuminate\Support\Facades\Auth;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use Illuminate\Validation\ValidationException;

class ClientLogin extends AuthLogin
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getDocumentTypeFormComponent(),
                        $this->getDocumentNumberFormComponent(),
                        // Usamos Turnstile en lugar de GRecaptcha
                        Turnstile::make('captcha')
                            ->label('Captcha')
                            ->theme('light') // Puedes usar 'light', 'dark', o 'auto'
                            ->language('es') 
                            ->size('normal'), 
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getDocumentTypeFormComponent(): Component
    {
        return Select::make('document_type')
            ->label(__('Tipo de documento de identificación'))
            ->options([
                'NIT' => 'N.I.T.',
                'CC' => 'Cédula de Ciudadanía',
                'TI' => 'Tarjeta de Identidad',
                'RC' => 'Registro Civil',
                'CE' => 'Cédula de Extranjería',
                'PEP' => 'Permiso Especial de Permanencia',
            ])
            ->required()
            ->placeholder(__('Seleccione el Tipo de Identificación'));
    }

    protected function getDocumentNumberFormComponent(): Component
    {
        return TextInput::make('document_number')
            ->label(__('Documento de identificación'))
            ->required()
            ->placeholder(__('Ingrese su número de documento'))
            ->rules('required|min:6|max:20');
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        // Obtener el ID del tipo de documento
        $documentTypeId = DB::table('document_types')
            ->where('name', $data['document_type'])
            ->value('id');

        if (!$documentTypeId) {
            $this->addError('document_type', __('Tipo de documento no válido.'));
            // Emitir evento para reiniciar el CAPTCHA
            $this->dispatch('reset-captcha');
            return null;
        }

        // Buscar el usuario directamente con el número de documento y el tipo de documento
        $user = \App\Models\User::where('document_type_id', $documentTypeId)
            ->where('document_number', $data['document_number'])
            ->first();

        if ($user) {
            // Verificar si el usuario tiene el rol de "client"
            if (!$user->hasRole('client')) {
                $this->addError('document_number', __('Solo los clientes pueden acceder a este panel.'));
                // Emitir evento para reiniciar el CAPTCHA
                $this->dispatch('reset-captcha');
                return null;
            }

            // Si es un cliente válido, iniciar sesión
            Auth::login($user);
            session()->regenerate(); // Regenerar la sesión para evitar problemas

            return app(LoginResponse::class);
        }

        // Si el usuario no existe o no tiene permiso
        $this->addError('document_number', __('Este documento no se encuentra en nuestros registros.'));
        // Emitir evento para reiniciar el CAPTCHA
        $this->dispatch('reset-captcha');

        return null;
    }

    public function getHeading(): string|Htmlable
    {
        return __('Beta Clientes J.R. Maker');
    }

    protected function getFormSchema(): array
    {
        return [
            $this->getEmailFormComponent()->label('Correo electrónico'),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];
    }

    /**
     * 
     *
     * @param \Illuminate\Validation\ValidationException $exception
     * @return void
     */
    protected function onValidationError(ValidationException $exception): void
    {
        // Emitir evento para reiniciar el CAPTCHA
        $this->dispatch('reset-captcha');

        // Llamar al método de la clase padre para manejar los errores de validación
        parent::onValidationError($exception);
    }
}
