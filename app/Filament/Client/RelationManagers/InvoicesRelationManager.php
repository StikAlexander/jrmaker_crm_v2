<?php

namespace App\Filament\Resources\ClientUserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use App\Tables\Columns\ModelLinkColumn;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';
    protected static ?string $recordTitleAttribute = 'invoice_number';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ModelLinkColumn::make('invoice_number')
                ->label('Número de Factura')
                ->setViewType('view')
                ->sortable()
                ->searchable()
                ->limit(50)
                ->url(fn ($record) => route('filament.resources.invoices.view', ['record' => $record->getKey()]))  
                ->formatStateUsing(fn (string $state): string => 'FEVD' . $state),
                
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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
