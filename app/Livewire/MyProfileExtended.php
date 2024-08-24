<?php

namespace App\Livewire;

use App\Filament\Pages\Auth\VerifyPasswordChange as AuthVerifyPasswordChange;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function Filament\Support\is_app_url;

class MyProfileExtended extends MyProfileComponent
{
    public ?array $data = [];

    public $user;

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $data = $this->getUser()->attributesToArray();
        $this->form->fill($data);
    }

    public function getUser(): Authenticatable & Model
    {
        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            throw new Exception('The authenticated user object must be an Eloquent model to allow the profile page to update it.');
        }

        return $user;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('media')
                    ->label('Avatar')
                    ->collection('avatars')
                    ->label('Foto')
                    ->avatar()
                    ->required(),
                Grid::make()->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->disabled()
                        ->required(),
                    TextInput::make('email')
                        ->label('Email')
                        ->disabled()
                        ->required(),
                ]),
            ])
            ->operation('edit')
            ->model($this->getUser())
            ->statePath('data');
    }

    public function submit()
    {
        try {
            // Recuperar el estado del formulario
            $data = $this->form->getState();

            // Solo pasar los datos relacionados con la foto de perfil
            $this->handleRecordUpdate($this->getUser(), [
                'media' => $data['media'] ?? null,
            ]);

            Notification::make()
                ->title('Actualización de perfil exitosa')
                ->success()
                ->send();

            $this->redirect('my-profile', navigate: FilamentView::hasSpaMode() && is_app_url('my-profile'));
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Fallo en la actualización')
                ->danger()
                ->send();
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Solo actualiza la foto de perfil
        if (isset($data['media'])) {
            $record->clearMediaCollection('avatars');
            $record->addMedia($data['media'])->toMediaCollection('avatars');
        }

        return $record;
    }

    public function render(): View
    {
        return view("livewire.my-profile-extended");
    }
}
