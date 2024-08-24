<?php

namespace App\Filament\Pages\Setting;

use App\Services\FileService;
use App\Settings\GeneralSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;
use Riodwanto\FilamentAceEditor\AceEditor;

use function Filament\Support\is_app_url;

class ManageGeneral extends SettingsPage
{
    use HasPageShield;

    protected static string $settings = GeneralSettings::class;
    protected static ?int $navigationSort = 99;
    protected static ?string $navigationIcon = 'fluentui-settings-20';

    public ?array $data = [];
    public string $themePath = '';
    public string $twConfigPath = '';

    public function mount(): void
    {
        $this->themePath = resource_path('css/filament/admin/theme.css');
        $this->twConfigPath = resource_path('css/filament/admin/tailwind.config.js');
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $settings = app(static::getSettings());
        $data = $this->mutateFormDataBeforeFill($settings->toArray());

        $fileService = new FileService;
        $data['theme-editor'] = $fileService->readfile($this->themePath);
        $data['tw-config-editor'] = $fileService->readfile($this->twConfigPath);

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('general.general_settings.sections.site'))
                    ->label(__('general.general_settings.sections.site'))
                    ->description(__('general.general_settings.sections.site.description'))
                    ->icon('fluentui-web-asset-24-o')
                    ->schema([
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\TextInput::make('brand_name')
                                ->label(__('general.general_settings.fields.brand_name'))
                                ->required(),
                            Forms\Components\Select::make('site_active')
                                ->label(__('general.general_settings.fields.site_active'))
                                ->options([
                                    0 => __('Not Active'),
                                    1 => __('Active'),
                                ])
                                ->native(false)
                                ->required(),
                        ]),
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\TextInput::make('brand_logoHeight')
                                    ->label(__('general.general_settings.fields.brand_logoHeight'))
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\FileUpload::make('brand_logo')
                                    ->label(__('general.general_settings.fields.brand_logo'))
                                    ->image()
                                    ->directory('sites')
                                    ->visibility('public')
                                    ->moveFiles()
                                    ->required()
                                    ->columnSpan(2),
                            ])
                                ->columnSpan(2),
                            Forms\Components\FileUpload::make('site_favicon')
                                ->label(__('general.general_settings.fields.site_favicon'))
                                ->image()
                                ->directory('sites')
                                ->visibility('public')
                                ->moveFiles()
                                ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon'])
                                ->required(),
                        ])->columns(4),
                    ]),
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('general.Color Palette'))
                            ->schema([
                                Forms\Components\ColorPicker::make('site_theme.primary')
                                    ->label(__('general.general_settings.fields.primary'))->rgb(),
                                Forms\Components\ColorPicker::make('site_theme.secondary')
                                    ->label(__('general.general_settings.fields.secondary'))->rgb(),
                                Forms\Components\ColorPicker::make('site_theme.gray')
                                    ->label(__('general.general_settings.fields.gray'))->rgb(),
                                Forms\Components\ColorPicker::make('site_theme.success')
                                    ->label(__('general.general_settings.fields.success'))->rgb(),
                                Forms\Components\ColorPicker::make('site_theme.danger')
                                    ->label(__('general.general_settings.fields.danger'))->rgb(),
                                Forms\Components\ColorPicker::make('site_theme.info')
                                    ->label(__('general.general_settings.fields.info'))->rgb(),
                                Forms\Components\ColorPicker::make('site_theme.warning')
                                    ->label(__('general.general_settings.fields.warning'))->rgb(),
                            ])
                            ->columns(3),
                        Forms\Components\Tabs\Tab::make(__('general.Code Editor'))
                            ->schema([
                                Forms\Components\Grid::make()->schema([
                                    AceEditor::make('theme-editor')
                                        ->label('theme.css')
                                        ->mode('css')
                                        ->height('24rem'),
                                    AceEditor::make('tw-config-editor')
                                        ->label('tailwind.config.js')
                                        ->height('24rem')
                                ])
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->mutateFormDataBeforeSave($this->form->getState());

            $settings = app(static::getSettings());

            $settings->fill($data);
            $settings->save();

            $fileService = new FileService;
            $fileService->writeFile($this->themePath, $data['theme-editor']);
            $fileService->writeFile($this->twConfigPath, $data['tw-config-editor']);

            Notification::make()
                ->title(__('general.settings_updated'))
                ->success()
                ->send();

            $this->redirect(static::getUrl(), [
                'navigate' => FilamentView::hasSpaMode() && is_app_url(static::getUrl()),
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function getNavigationGroup(): ?string
    {
        return __('general.configuracion');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.general');
    }

    public function getTitle(): string|Htmlable
    {
        return __('general.configuracion_general');
    }

    public function getHeading(): string|Htmlable
    {
        return __('general.configuracion_general');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('general.subheading');
    }
}
