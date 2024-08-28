<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminUserResource\Pages;
use App\Models\AdminUser;
use App\Models\User;
use App\Settings\MailSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use App\Notifications\VerifyEmail;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail as AuthVerifyEmail;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\MaxWidth;

class AdminUserResource extends Resource implements HasMedia
{
    
    use InteractsWithMedia;

    protected static ?string $model = AdminUser::class;
    

    public static function getEloquentQuery(): Builder
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        });
    }

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Usuarios';
    protected static ?int $navigationSort = 3;
    protected static ?string $pluralLabel = 'Administradores';
    protected static ?string $singularLabel = 'Administrador';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Datos Generales')
                            ->description('Incluye los datos principales del colaborador')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Forms\Components\SpatieMediaLibraryFileUpload::make('media')
                                            ->hiddenLabel()
                                            ->avatar()
                                            ->collection('avatars')
                                            ->alignCenter()
                                            ->columnSpanFull(),
    
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('Nombre completo')
                                            ->columnSpan(2),
    
                                        Forms\Components\Select::make('document_type_id')
                                            ->label('Tipo de identificación')
                                            ->relationship('documentType', 'name')
                                            ->required()
                                            ->columnSpan(1),
    
                                        Forms\Components\TextInput::make('document_number')
                                            ->label('Identificación')
                                            ->required()
                                            ->rules(['regex:/^[0-9]+$/'])
                                            ->maxLength(20)
                                            ->helperText('Solo se permiten números.')
                                            ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*'])
                                            ->numeric()
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->columns(1),
    
                        Section::make('Información de Contacto')
                            ->description('Agrega los datos de contacto para este colaborador')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->label('Correo electrónico')
                                            ->columnSpan(1),
    
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->required()
                                            ->rules(['regex:/^[0-9]+$/'])
                                            ->maxLength(20)
                                            ->helperText('Solo se permiten números.')
                                            ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*'])
                                            ->numeric()
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->columns(1),
    
                        Section::make('Contraseña')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('password')
                                            ->password()
                                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                            ->dehydrated(fn (?string $state): bool => filled($state))
                                            ->revealable()
                                            ->required()
                                            ->columnSpan(1),
    
                                        Forms\Components\TextInput::make('passwordConfirmation')
                                            ->password()
                                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                            ->dehydrated(fn (?string $state): bool => filled($state))
                                            ->revealable()
                                            ->same('password')
                                            ->required()
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->columns(1)
                            ->hidden(fn (string $operation): bool => in_array($operation, ['edit', 'view'])),
    
                        Section::make('Verificación de Correo Electrónico')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('email_verified_at')
                                            ->label('Fecha de verificación del correo')
                                            ->content(fn (?User $record): ?string => $record?->email_verified_at)
                                            ->columnSpan(1),
    
                                        Forms\Components\Actions::make([
                                            Action::make('resend_verification')
                                                ->label('Reenviar verificación')
                                                ->color('secondary')
                                                ->action(fn (?MailSettings $settings, ?User $record) => $record ? static::doResendEmailVerification($settings, $record) : null)
                                                ->hidden(fn (?User $user) => $user?->email_verified_at != null),
                                        ])
                                        ->columnSpanFull(),
    
                                        Forms\Components\Placeholder::make('created_at')
                                            ->label('Fecha de creación')
                                            ->content(fn (?User $record): ?string => $record?->created_at?->diffForHumans())
                                            ->columnSpan(1),
    
                                        Forms\Components\Placeholder::make('updated_at')
                                            ->label('Fecha de actualización')
                                            ->content(fn (?User $record): ?string => $record?->updated_at?->diffForHumans())
                                            ->columnSpan(1),
    
                                        Forms\Components\Placeholder::make('created_by')
                                            ->label('Creado por')
                                            ->content(fn (?User $record): string => $record ? ($record->createdBy?->name ?? '-') : '-')
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->visible(fn (string $operation): bool => in_array($operation, ['view', 'edit']))
                            ->columns(1),
                    ])
                    ->maxWidth(MaxWidth::FiveExtraLarge)  // Limita el ancho de la tarjeta
                    ->extraAttributes([
                        'class' => 'mx-auto mt-10',  // Centra la tarjeta en la pantalla
                    ]),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('media')
                    ->label('Foto de perfil')
                    ->collection('avatars')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Identificación')
                    ->searchable(),            
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->hidden(true),
                    
                Tables\Columns\TextColumn::make('documentType.name')
                    ->label('Tipo de identificación')
                    ->sortable()
                    ->searchable()
                    ->hidden(true),
                    
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Creado por')
                    ->placeholder('-')
                    ->sortable()
                    ->searchable()
                    ->hidden(true),        
                    
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Fecha verificación email')
                    ->dateTime()
                    ->sortable()
                    ->hidden(true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),

                Tables\Actions\Action::make('toggleStatus')
                ->icon('heroicon-o-light-bulb')
                ->label('') 
                ->action(function ($record) {
                    $newStatus = $record->status === 'active' ? 'inactive' : 'active';
                    $record->update(['status' => $newStatus]);
                })
                ->color(fn ($record) => $record->status === 'active' ? 'success' : 'danger')
                ->tooltip(fn ($record) => $record->status === 'active' ? 'Desactivar' : 'Activar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
    

    public static function getRelations(): array
    {
        return [
            // Agrega relaciones si es necesario
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'edit' => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }

    public static function doResendEmailVerification($settings = null, User $record): void
    {
        if (! method_exists($record, 'notify')) {
            $userClass = $record::class;
            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }
    
        $notification = new AuthVerifyEmail();
        $notification->url = Filament::getVerifyEmailUrl($record);
    
        $settings->loadMailSettingsToConfig();
    
        $record->notify($notification);
    
        Notification::make()
            ->title(__('resource.user.notifications.notification_resent.title'))
            ->success()
            ->send();
    }
    
}

