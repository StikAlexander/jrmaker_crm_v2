<?php

namespace App\Filament\Client\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Invoice;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class Invoices extends BaseWidget
{

    // Configurar el widget para que ocupe el ancho completo
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Invoice::query()
            ->where('client_id', auth()->id());
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('invoice_number')
                ->label('Numero de Factura')
                ->sortable(),
            Tables\Columns\TextColumn::make('issue_date')
                ->label('Fecha de Emisión')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('due_date')
                ->label('Fecha de Vencimiento')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('total_amount')
                ->label('Monto Total')
                ->money('COP')
                ->sortable(),
            Tables\Columns\TextColumn::make('pending_amount')
                ->label('Monto Pendiente')
                ->money('COP')
                ->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->label('Estado')
                ->badge()
                ->sortable(),
        ];
    }

    // Aplica una clase CSS para asegurarse de que el widget ocupe todo el ancho disponible
    protected function getTableClass(): ?string
    {
        return 'w-full';  // Hace que el widget ocupe todo el ancho disponible
    }
}
