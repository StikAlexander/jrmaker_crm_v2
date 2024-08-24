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
                Forms\Components\Section::make(__('page.general_settings.sections.site'))
                    ->label(__('page.general_settings.sections.site'))  // Forzando la traducción directamente
                    ->description(__('page.general_settings.sections.site.description'))  // Forzando la traducción directamente
                    ->icon('fluentui-web-asset-24-o')
                    ->schema([
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\TextInput::make('brand_name')
                                ->label(__('page.general_settings.fields.brand_name'))  // Forzando la traducción directamente
                                ->required(),
                            Forms\Components\Select::make('site_active')
                                ->label(__('page.general_settings.fields.site_active'))  // Forzando la traducción directamente
                                ->options([
                                    0 => __("Not Active"),
                                    1 => __("Active"),
                                ])
                                ->native(false)
                                ->required(),
                        ]),
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\TextInput::make('brand_logoHeight')
                                    ->label(__('page.general_settings.fields.brand_logoHeight'))  // Forzando la traducción directamente
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\FileUpload::make('brand_logo')
                                    ->label(__('page.general_settings.fields.brand_logo'))  // Forzando la traducción directamente
                                    ->image()
                                    ->directory('sites')
                                    ->visibility('public')
                                    ->moveFiles()
                                    ->required()
                                    ->columnSpan(2),
                            ])
                                ->columnSpan(2),
                            Forms\Components\FileUpload::make('site_favicon')
                                ->label(__('page.general_settings.fields.site_favicon'))  // Forzando la traducción directamente
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
                        Forms\Components\Tabs\Tab::make(__('Color Palette'))  // Forzando la traducción directamente
                            ->schema([
                                Forms\Components\ColorPicker::make('site_theme.primary')
                                    ->label(__('page.general_settings.fields.primary'))->rgb(),  // Forzando la traducción directamente
                                Forms\Components\ColorPicker::make('site_theme.secondary')
                                    ->label(__('page.general_settings.fields.secondary'))->rgb(),  // Forzando la traducción directamente
                                Forms\Components\ColorPicker::make('site_theme.gray')
                                    ->label(__('page.general_settings.fields.gray'))->rgb(),  // Forzando la traducción directamente
                                Forms\Components\ColorPicker::make('site_theme.success')
                                    ->label(__('page.general_settings.fields.success'))->rgb(),  // Forzando la traducción directamente
                                Forms\Components\ColorPicker::make('site_theme.danger')
                                    ->label(__('page.general_settings.fields.danger'))->rgb(),  // Forzando la traducción directamente
                                Forms\Components\ColorPicker::make('site_theme.info')
                                    ->label(__('page.general_settings.fields.info'))->rgb(),  // Forzando la traducción directamente
                                Forms\Components\ColorPicker::make('site_theme.warning')
                                    ->label(__('page.general_settings.fields.warning'))->rgb(),  // Forzando la traducción directamente
                            ])
                            ->columns(3),
                        Forms\Components\Tabs\Tab::make(__('Code Editor'))  // Forzando la traducción directamente
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
                ->title(__('Settings updated.'))  // Forzando la traducción directamente
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
        return __('Configuración'); // Traducido
    }
    
    public static function getNavigationLabel(): string
    {
        return __('General'); // Traducido
    }
    
    public function getTitle(): string|Htmlable
    {
        return __('Configuración general'); // Traducido
    }
    
    public function getHeading(): string|Htmlable
    {
        return __('Configuración general'); // Traducido
    }
    
    public function getSubheading(): string|Htmlable|null
    {
        return __('Administre la configuración general del sitio aquí.'); // Traducido
    }
}
