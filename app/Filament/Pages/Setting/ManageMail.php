<?php

namespace App\Filament\Pages\Setting;

use App\Mail\TestMail;
use App\Settings\MailSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Mail;

use function Filament\Support\is_app_url;

class ManageMail extends SettingsPage
{
    use HasPageShield;

    protected static string $settings = MailSettings::class;

    protected static ?int $navigationSort = 99;
    protected static ?string $navigationIcon = 'fluentui-mail-settings-20';

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $data = $this->mutateFormDataBeforeFill(app(static::getSettings())->toArray());

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Configuration')
                            ->label('Configuración') // Uso directo para forzar traducción temporal
                            ->icon('fluentui-calendar-settings-32-o')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('driver')->label('Conductor') // Uso directo para forzar traducción temporal
                                            ->options([
                                                "smtp" => "SMTP (Recommended)",
                                                "mailgun" => "Mailgun",
                                                "ses" => "Amazon SES",
                                                "postmark" => "Postmark",
                                            ])
                                            ->native(false)
                                            ->required()
                                            ->columnSpan(2),
                                        Forms\Components\TextInput::make('host')->label('Anfitrión') // Uso directo para forzar traducción temporal
                                            ->required(),
                                        Forms\Components\TextInput::make('port')->label('Puerto') // Uso directo para forzar traducción temporal
                                            ->required(),
                                        Forms\Components\Select::make('encryption')->label('Cifrado') // Uso directo para forzar traducción temporal
                                            ->options([
                                                "ssl" => "SSL",
                                                "tls" => "TLS",
                                            ])
                                            ->native(false),
                                        Forms\Components\TextInput::make('timeout')->label('Se acabó el tiempo') // Uso directo para forzar traducción temporal
                                            ->required(),
                                        Forms\Components\TextInput::make('username')->label('Nombre de usuario') // Uso directo para forzar traducción temporal
                                            ->required(),
                                        Forms\Components\TextInput::make('password')->label('Contraseña') // Uso directo para forzar traducción temporal
                                            ->password()
                                            ->revealable(),
                                    ])
                                    ->columns(3),
                            ])
                    ])
                    ->columnSpan([
                        "md" => 2
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('From (Sender)')
                            ->label('De (remitente)') // Uso directo para forzar traducción temporal
                            ->icon('fluentui-person-mail-48-o')
                            ->schema([
                                Forms\Components\TextInput::make('from_address')->label('Correo electrónico') // Uso directo para forzar traducción temporal
                                    ->required(),
                                Forms\Components\TextInput::make('from_name')->label('Nombre') // Uso directo para forzar traducción temporal
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Mail to')
                            ->label('Enviar por correo a') // Uso directo para forzar traducción temporal
                            ->schema([
                                Forms\Components\TextInput::make('mail_to')
                                    ->label('Correo electrónico del receptor') // Uso directo para forzar traducción temporal
                                    ->hiddenLabel()
                                    ->placeholder('Correo electrónico del receptor') // Uso directo para forzar traducción temporal
                                    ->required(),
                                Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('Enviar correo de prueba') // Uso directo para forzar traducción temporal
                                            ->label('Enviar correo de prueba') // Uso directo para forzar traducción temporal
                                            ->action('sendTestMail')
                                            ->color('warning')
                                            ->icon('fluentui-mail-alert-28-o')
                                    ])->fullWidth(),
                            ])
                    ])
                    ->columnSpan([
                        "md" => 1
                    ]),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function save(MailSettings $settings = null): void
    {
        try {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $settings->fill($data);
            $settings->save();

            $this->callHook('afterSave');

            $this->sendSuccessNotification('Configuración de correo actualizada.'); // Uso directo para forzar traducción temporal

            $this->redirect(static::getUrl(), navigate: FilamentView::hasSpaMode() && is_app_url(static::getUrl()));
        } catch (\Throwable $th) {
            $this->sendErrorNotification('Error al actualizar la configuración: ' . $th->getMessage()); // Uso directo para forzar traducción temporal
            throw $th;
        }
    }

    public function sendTestMail(MailSettings $settings = null)
    {
        $data = $this->form->getState();

        $settings->loadMailSettingsToConfig($data);
        try {
            $mailTo = $data['mail_to'];
            $mailData = [
                'title' => 'Este es un correo de prueba para verificar la configuración de SMTP', // Uso directo para forzar traducción temporal
                'body' => 'Esto es para probar el envío de correo utilizando SMTP.', // Uso directo para forzar traducción temporal
            ];

            Mail::to($mailTo)->send(new TestMail($mailData));

            $this->sendSuccessNotification('Correo enviado a: ' . $mailTo); // Uso directo para forzar traducción temporal
        } catch (\Exception $e) {
            $this->sendErrorNotification('Error al enviar correo: ' . $e->getMessage()); // Uso directo para forzar traducción temporal
        }
    }

    public function sendSuccessNotification($title)
    {
        Notification::make()
                ->title($title)
                ->success()
                ->send();
    }

    public function sendErrorNotification($title)
    {
        Notification::make()
                ->title($title)
                ->danger()
                ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Configuración'; // Uso directo para forzar traducción temporal
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Correo'; // Uso directo para forzar traducción temporal
    }
    
    public function getTitle(): string|Htmlable
    {
        return 'Configuración de correo'; // Uso directo para forzar traducción temporal
    }
    
    public function getHeading(): string|Htmlable
    {
        return 'Configuración de correo'; // Uso directo para forzar traducción temporal
    }
    
    public function getSubheading(): string|Htmlable|null
    {
        return 'Gestione la configuración del correo electrónico aquí.'; // Uso directo para forzar traducción temporal
    }
}
