<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientUserResource\Pages;
use App\Models\ClientUser;
use App\Models\User;
use App\Settings\MailSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;


class ClientUserResource extends Resource implements HasMedia
{
    use InteractsWithMedia;

    protected static ?string $model = ClientUser::class;

    public static function getEloquentQuery(): Builder
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'client');
        });
    }

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Usuarios';
    protected static ?int $navigationSort = 1;
    protected static ?string $pluralLabel = 'Clientes';
    protected static ?string $singularLabel = 'Client';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        // Otros campos...
                        Forms\Components\TextInput::make('document_number')
                            ->label('Identificación')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->maxLength(20),
                        Forms\Components\Select::make('document_type_id')
                            ->label('Document Type')
                            ->relationship('documentType', 'name')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
                // Ocultar los campos de contraseña
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->hidden(true),
                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->hidden(true),
                    ])
                    ->hidden(true),
            ])
            ->columns(3);
    }
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('media')
                    ->label('Foto')
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
                    ->searchable()
                    ->hidden(true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->colors(['info'])
                    ->badge(),
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
                Impersonate::make('impersonate')
                ->redirectTo(fn ($record) => $record->hasRole('client') ? '/client' : '/admin'),
                ])
                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                        Tables\Actions\RestoreBulkAction::make(),
                    ]),
                ]);
        }
    
        public static function getPages(): array
        {
            return [
                'index' => Pages\ListClientUsers::route('/'),
                'create' => Pages\CreateClientUser::route('/create'),
                'edit' => Pages\EditClientUser::route('/{record}/edit'),
            ];
        }
    }


