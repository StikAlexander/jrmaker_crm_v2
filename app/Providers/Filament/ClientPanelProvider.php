<?php

namespace App\Providers\Filament;


use App\Filament\Auth\ClientLogin;
use App\Filament\Pages\Auth\ClientLogin as AuthClientLogin;
use App\Http\Middleware\AuthenticateClient;
use App\Livewire\MyProfileClientExtended;
use App\Settings\GeneralSettings;
use EightyNine\Reports\ReportsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Shanerbaner82\PanelRoles\PanelRoles;

class ClientPanelProvider extends PanelProvider
{
// ClientPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('client')
        ->path('client')
        ->login(AuthClientLogin::class)
        ->colors([
        'primary' => 'rgb(171, 83, 79)',    // Rojo terracota (#ab534f) para botones y elementos clave
        'secondary' => 'rgb(53, 65, 83)',   // Azul oscuro (#354153) para textos y detalles secundarios
        'gray' => 'rgb(0, 0, 0)',           // Negro puro para texto y bordes generales
        'success' => 'rgb(12, 195, 178)',   // Verde azulado (solo para elementos de éxito, no para texto)
        'danger' => 'rgb(199, 29, 81)',     // Rojo vibrante para alertas y errores
        'info' => 'rgb(113, 12, 195)',      // Púrpura vibrante para información adicional
        'warning' => 'rgb(255, 186, 93)',   // Amarillo cálido para advertencias y alertas
        'accent' => 'rgb(171, 83, 79)',     // Rojo terracota para acentos adicionales
        ])
        ->favicon(fn (GeneralSettings $settings) => Storage::url($settings->site_favicon))
        ->brandName(fn (GeneralSettings $settings) => $settings->brand_name)
        ->brandLogo(fn (GeneralSettings $settings) => Storage::url($settings->brand_logo))
        ->brandLogoHeight(fn (GeneralSettings $settings) => $settings->brand_logoHeight)
        ->discoverResources(in: app_path('Filament/Client/Resources'), for: 'App\\Filament\\Client\\Resources')
        ->discoverPages(in: app_path('Filament/Client/Pages'), for: 'App\\Filament\\Client\\Pages')
        ->pages([
            \App\Filament\Client\Pages\Dashboard::class,  // Asegúrate de incluir esta línea
        ])
        ->discoverWidgets(in: app_path('Filament/Client/Widgets'), for: 'App\\Filament\\Client\\Widgets')
        ->viteTheme('resources/css/filament/client/theme.css')
        ->databaseNotifications()
        ->databaseNotificationsPolling('30s')
        ->widgets([
            Widgets\AccountWidget::class,
        ])
        ->middleware([
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ])
        ->authMiddleware([
            AuthenticateClient::class,
        ])
        ->plugins([
            ReportsPlugin::make(), 
            \BezhanSalleh\FilamentExceptions\FilamentExceptionsPlugin::make(),
            \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                ->gridColumns([
                    'default' => 2,
                    'sm' => 1
                ])
                ->sectionColumnSpan(1)
                ->checkboxListColumns([
                    'default' => 1,
                    'sm' => 2,
                    'lg' => 3,
                ])
                ->resourceCheckboxListColumns([
                    'default' => 1,
                    'sm' => 2,
                ]),
                \Jeffgreco13\FilamentBreezy\BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    shouldRegisterNavigation: false,
                    navigationGroup: 'Settings',
                    hasAvatars: true,
                    slug: 'my-profile'
                )
                ->withoutMyProfileComponents([
                    'update_password' // Excluir el componente de actualización de contraseñas
                ])
                ->myProfileComponents([
                    'personal_info' => MyProfileClientExtended::class, // Solo información personal
                ]),
            PanelRoles::make()
                ->restrictedRoles(['client']),             
            ]);
        }
    }
