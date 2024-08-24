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
                        Forms\Components\Section::make(__('mail.configuration'))
                            ->label(__('mail.configuration')) 
                            ->icon('fluentui-calendar-settings-32-o')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('driver')->label(__('mail.driver')) 
                                            ->options([
                                                "smtp" => __('mail.smtp'),
                                                "mailgun" => __('mail.mailgun'),
                                                "ses" => __('mail.ses'),
                                                "postmark" => __('mail.postmark'),
                                            ])
                                            ->native(false)
                                            ->required()
                                            ->columnSpan(2),
                                        Forms\Components\TextInput::make('host')->label(__('mail.host')) 
                                            ->required(),
                                        Forms\Components\TextInput::make('port')->label(__('mail.port')) 
                                            ->required(),
                                        Forms\Components\Select::make('encryption')->label(__('mail.encryption')) 
                                            ->options([
                                                "ssl" => "SSL",
                                                "tls" => "TLS",
                                            ])
                                            ->native(false),
                                        Forms\Components\TextInput::make('timeout')->label(__('mail.timeout')) 
                                            ->required(),
                                        Forms\Components\TextInput::make('username')->label(__('mail.username')) 
                                            ->required(),
                                        Forms\Components\TextInput::make('password')->label(__('mail.password')) 
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
                        Forms\Components\Section::make(__('mail.from.sender'))
                            ->label(__('mail.from.sender')) 
                            ->icon('fluentui-person-mail-48-o')
                            ->schema([
                                Forms\Components\TextInput::make('from_address')->label(__('mail.from.address')) 
                                    ->required(),
                                Forms\Components\TextInput::make('from_name')->label(__('mail.from.name')) 
                                    ->required(),
                            ]),

                        Forms\Components\Section::make(__('mail.mail_to'))
                            ->label(__('mail.mail_to')) 
                            ->schema([
                                Forms\Components\TextInput::make('mail_to')
                                    ->label(__('mail.receiver_email')) 
                                    ->hiddenLabel()
                                    ->placeholder(__('mail.receiver_email')) 
                                    ->required(),
                                Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make(__('mail.send_test_mail')) 
                                            ->label(__('mail.send_test_mail')) 
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

            $this->sendSuccessNotification(__('mail.mail_config_updated')); 

            $this->redirect(static::getUrl(), navigate: FilamentView::hasSpaMode() && is_app_url(static::getUrl()));
        } catch (\Throwable $th) {
            $this->sendErrorNotification(__('mail.error_updating', ['error' => $th->getMessage()]));
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
                'title' => __('mail.test_mail_title'),
                'body' => __('mail.test_mail_body'),
            ];

            Mail::to($mailTo)->send(new TestMail($mailData));

            $this->sendSuccessNotification(__('mail.mail_sent_to', ['mailTo' => $mailTo]));
        } catch (\Exception $e) {
            $this->sendErrorNotification(__('mail.error_sending_mail', ['error' => $e->getMessage()]));
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
        return __('mail.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('mail.navigation.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('mail.heading.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('mail.heading.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('mail.heading.subheading');
    }
}
