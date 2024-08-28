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
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\MaxWidth;


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
                Card::make()
                    ->schema([
                        Section::make('Datos Generales')
                            ->description('Incluye los datos principales de tu cliente')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
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
                                    ]),
                            ])
                            ->columns(1),
    
                        Section::make('Información de Contacto')
                            ->description('Agrega los datos de contacto para este cliente')
                            ->schema([
                                Grid::make(2)
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
                                            ->hidden(fn ($livewire) => $livewire instanceof \App\Filament\Resources\ClientUserResource\Pages\CreateClientUser)
                                            ->columnSpan(1),
                                    ]),
                            ])
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
            
            Tables\Actions\EditAction::make()
                ->modalHeading('Editar Cliente') // Personaliza el título del modal de edición
                ->modalWidth('4xl'), // Define el tamaño del modal (Falta el punto y coma aquí)
        
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
            
            Impersonate::make('impersonate')
                ->redirectTo(fn ($record) => $record->hasRole('client') ? '/client' : '/admin'),
        
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

}   