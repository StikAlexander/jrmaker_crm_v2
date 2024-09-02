<?php

namespace App\Filament\Resources\ClientUserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms;

namespace App\Filament\Resources\ClientUserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';
    protected static ?string $recordTitleAttribute = 'invoice_number';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Número de Factura')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Monto Total')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'Paid',
                        'danger' => 'Cancelled',
                    ]),
            ])
            ->filters([
                // Aquí puedes añadir filtros si lo deseas
            ])
            ->headerActions([
                // Acciones de encabezado, como "Crear nuevo"
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
