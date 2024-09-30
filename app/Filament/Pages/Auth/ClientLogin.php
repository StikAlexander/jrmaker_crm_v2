<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as AuthLogin;
use AbanoubNassem\FilamentGRecaptchaField\Forms\Components\GRecaptcha;
use Illuminate\Support\Facades\Auth;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Htmlable;

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
                        GRecaptcha::make('captcha')
                            ->label('Captcha')
                            ->rules('required'),  
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
            return null;
        }
    
        // Buscar el usuario directamente con el número de documento y el tipo de documento
        $user = \App\Models\User::where('document_type_id', $documentTypeId)
            ->where('document_number', $data['document_number'])
            ->first();
    
        if ($user) {
            // Verificar si el usuario tiene el rol de "client"
            if (!$user->hasRole('client')) {
                // Rechazar acceso si no es cliente
                $this->addError('document_number', __('Solo los clientes pueden acceder a este panel.'));
                return null; // No iniciar sesión, no autenticar
            }
    
            // Si es un cliente válido, iniciar sesión
            Auth::login($user);
            session()->regenerate(); // Regenerar la sesión para evitar problemas de fijación de sesión
    
            return app(LoginResponse::class);
        }
    
        // Si el usuario no existe o no tiene permiso
        $this->addError('document_number', __('Este documento no se encuentra en nuestros registros.'));
        return null;
    }
    
    public function getHeading(): string|Htmlable
    {
        return __('Panel de Clientes J.R. MAKER');
    }

    protected function getFormSchema(): array
    {
        return [
            $this->getEmailFormComponent()->label('Correo electrónico'),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];
    }
}
