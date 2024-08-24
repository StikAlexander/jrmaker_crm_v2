<?php

namespace App\Livewire;

use App\Filament\Pages\Auth\VerifyPasswordChange;
use Filament\Facades\Filament;
use Jeffgreco13\FilamentBreezy\Livewire\UpdatePassword as BaseUpdatePassword;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Illuminate\Validation\Rules\Password;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UpdatePasswordCustom extends BaseUpdatePassword
{
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    public $verification_code;
    public $codeSent = false;

    protected string $view = 'livewire.update-password-custom';

    public function mount()
    {
        $this->resetFormFields();
    }

    public function resetFormFields()
    {
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->verification_code = '';
        $this->user = Filament::getCurrentPanel()->auth()->user();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('current_password')
                    ->label('Contraseña actual')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rules('current_password'),

                TextInput::make('new_password')
                    ->label('Nueva contraseña')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rules([
                        Password::min(8)->mixedCase()->uncompromised(3),
                    ]),

                TextInput::make('new_password_confirmation')
                    ->label('Confirme la nueva contraseña')
                    ->password()
                    ->revealable()
                    ->required()
                    ->same('new_password'),

                TextInput::make('verification_code')
                    ->label('Código de Verificación')
                    ->visible($this->codeSent)
                    ->required($this->codeSent),
            ]);
    }

    public function sendVerificationCode()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|different:current_password',
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        if (!Hash::check($this->current_password, $this->user->password)) {
            $this->addError('current_password', 'La contraseña actual es incorrecta.');
            return;
        }

        $code = mt_rand(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $this->user->email],
            ['token' => $code, 'created_at' => now()]
        );

        $this->user->notify(new VerifyPasswordChange($code));

        $this->codeSent = true;
    }

    public function verifyCode()
    {
        $this->validate([
            'verification_code' => 'required',
        ]);

        // Verificar si el código es válido
        $validCode = DB::table('password_reset_tokens')
            ->where('email', $this->user->email)
            ->where('token', $this->verification_code)
            ->exists();

        if (!$validCode) {
            $this->addError('verification_code', 'El código de verificación es incorrecto.');
            return;
        }

        // Actualizar la contraseña del usuario
        $this->user->update([
            'password' => Hash::make($this->new_password),
        ]);

        // Eliminar el código de verificación
        DB::table('password_reset_tokens')
            ->where('email', $this->user->email)
            ->delete();

        Notification::make()
            ->title(__('Contraseña actualizada correctamente'))
            ->success()
            ->send();

        // Limpiar el formulario
        $this->resetFormFields();
    }

    public function submit(): void
    {
        if (!$this->codeSent) {
            $this->sendVerificationCode();
            Notification::make()
                ->title('Código enviado. Verifica tu correo.')
                ->success()
                ->send();
            return;
        }

        $this->verifyCode();
    }
}
