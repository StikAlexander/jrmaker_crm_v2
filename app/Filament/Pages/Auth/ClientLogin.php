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
use Illuminate\Support\Facades\Crypt;
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

    // Define el método getDocumentTypeFormComponent
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

    // Define el método getDocumentNumberFormComponent
    protected function getDocumentNumberFormComponent(): Component
    {
        return TextInput::make('document_number')
            ->label(__('Documento de identificación'))
            ->required()
            ->placeholder(__('Ingrese su número de documento'))
            ->rules('required|min:6|max:20'); // Ajusta las reglas según los tipos de documento.
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        // Verifica los datos recibidos
        Log::info('Datos recibidos del formulario:', $data);

        // Obtener el ID del tipo de documento
        $documentTypeId = DB::table('document_types')
            ->where('name', $data['document_type'])
            ->value('id');

        if (!$documentTypeId) {
            $this->addError('document_type', __('Tipo de documento no válido.'));
            return null;
        }

        // Verifica si el document_type_id es correcto
        Log::info('ID de tipo de documento:', ['document_type_id' => $documentTypeId]);

        // Buscar usuarios con el tipo de documento
        $users = \App\Models\User::where('document_type_id', $documentTypeId)->get();

        // Loguear la cantidad de usuarios encontrados
        Log::info('Usuarios encontrados:', ['count' => $users->count()]);

        $user = $users->first(function ($user) use ($data) {
            // Mostrar el documento encriptado del usuario
            Log::info('Intentando desencriptar el número de documento del usuario:', ['encrypted_document' => $user->getRawOriginal('document_number')]);

            // Intentar desencriptar y comparar
            try {
                $decryptedDocumentNumber = Crypt::decryptString($user->getRawOriginal('document_number'));
                Log::info('Documento desencriptado:', ['decrypted_document' => $decryptedDocumentNumber]);

                return $decryptedDocumentNumber === $data['document_number'];
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                Log::error('Error de desencriptación:', ['error' => $e->getMessage()]);
                return false;
            }
        });

        if ($user) {
            Auth::login($user);
            session()->regenerate();

            return app(LoginResponse::class);
        }

        $this->addError('document_number', __('Este documento no se encuentra en nuestros registros.'));
        return null;
    }

    public function getHeading(): string|Htmlable
    {
        return __('Panel de Clientes J.R. MAKER'); // Título personalizado para el panel de clientes
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
