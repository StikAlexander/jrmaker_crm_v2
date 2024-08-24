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
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Section::make('Datos Generales')
                        ->description('Incluye los datos principales del administrador')
                        ->schema([
                            Forms\Components\Placeholder::make('')
                                ->content('Foto de perfil')
                                ->columnSpanFull()
                                ->extraAttributes(['style' => 'text-align: center; font-size: 0.875rem; color: #6b7280;']),
                    
                            Forms\Components\SpatieMediaLibraryFileUpload::make('media')
                                ->avatar()
                                ->collection('avatars')
                                ->label('')
                                ->alignCenter()
                                ->columnSpanFull(),
                    
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->label('Razón social / nombre completo')
                                ->columnSpan(2),
                    
                            Forms\Components\Select::make('document_type_id')
                                ->label('Tipo de identificación')
                                ->relationship('documentType', 'name')
                                ->required()
                                ->columnSpan(1),
                    
                            Forms\Components\TextInput::make('document_number')
                                ->label('Número de documento')
                                ->required()
                                ->rules(['regex:/^[0-9]+$/'])
                                ->maxLength(20)
                                ->helperText('Solo se permiten números.')
                                ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*'])
                                ->numeric()
                                ->columnSpan(1),
                        ])
                        ->columns(4),
 
                        Forms\Components\Section::make('Información de Contacto')
                            ->description('Agrega los datos de contacto para este administrador')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Correo electrónico')
                                    ->columnSpan(2),
    
                                Forms\Components\TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->required()
                                    ->rules(['regex:/^[0-9]+$/'])
                                    ->maxLength(20)
                                    ->helperText('Solo se permiten números.')
                                    ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*'])
                                    ->numeric()
                                    ->columnSpan(1),
    
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'suspended' => 'Suspended',
                                    ])
                                    ->default('active')
                                    ->required()
                                    ->hidden(fn ($livewire) => $livewire instanceof \App\Filament\Resources\AdminUserResource\Pages\CreateAdminUser)
                                    ->columnSpan(1),
                            ])
                            ->columns(4),
    
                        Forms\Components\Section::make('Contraseña')
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
                            ])
                            ->columns(2)
                            ->hidden(fn (string $operation): bool => $operation === 'edit'),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(4);
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
                    ->searchable()
                    ->hidden(true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => str::headline($state))
                    ->colors(['info'])
                    ->badge(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Fecha verificación')
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
            ->filters([
                // Agrega filtros si es necesario
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
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

