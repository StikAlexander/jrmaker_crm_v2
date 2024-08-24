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
use Filament\Forms\Components\NumericInput;


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
                // Encapsulamos todo dentro de un Card
                Forms\Components\Card::make()
                    ->schema([
                        // Sección de Datos Generales
                        Forms\Components\Section::make('Datos Generales')
                            ->description('Incluye los datos principales de tu cliente')
                            ->schema([
                                // Nombre Completo (2 columnas)
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Razón social / nombre completo')
                                    ->columnSpan(2),
    
                                // Tipo de Identificación (1 columna)
                                Forms\Components\Select::make('document_type_id')
                                    ->label('Tipo de identificación')
                                    ->relationship('documentType', 'name')
                                    ->required()
                                    ->columnSpan(1),
    
                                // Número de Documento (1 columna)
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
    
                        // Sección de Información de Contacto
                        Forms\Components\Section::make('Información de Contacto')
                            ->description('Agrega los datos de contacto para este cliente')
                            ->schema([
                                // Correo Electrónico (2 columnas)
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Correo electrónico')
                                    ->columnSpan(2),
    
                                // Teléfono (1 columna)
                                Forms\Components\TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->required()
                                    ->rules(['regex:/^[0-9]+$/'])
                                    ->maxLength(20)
                                    ->helperText('Solo se permiten números.')
                                    ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*'])
                                    ->numeric()
                                    ->columnSpan(1),
    
                                // Estado (1 columna)
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'suspended' => 'Suspended',
                                    ])
                                    ->default('active')
                                    ->required()
                                    ->hidden(fn ($livewire) => $livewire instanceof \App\Filament\Resources\ClientUserResource\Pages\CreateClientUser)
                                    ->columnSpan(1),
                            ])
                            ->columns(4),
                    ])
                    ->columnSpanFull(), // El Card ocupa todo el ancho
            ])
            ->columns(4); // Distribuye las columnas del formulario
    }
    
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('nombre')
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


