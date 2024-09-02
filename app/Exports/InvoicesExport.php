<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Invoice::all(); // Aquí puedes ajustar la consulta para incluir o excluir registros
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Número de Factura',
            'Fecha de Emisión',
            'Fecha de Vencimiento',
            'Monto Total',
            'Monto Pendiente',
            'Estado',
            'Descripción',
        ];
    }

    /**
     * @param Invoice $invoice
     * @return array
     */
    public function map($invoice): array
    {
        return [
            'FEVD' . $invoice->invoice_number,
            $invoice->issue_date->format('Y-m-d'),
            $invoice->due_date->format('Y-m-d'),
            $invoice->total_amount,
            $invoice->pending_amount,
            $invoice->status,
            $invoice->description,
        ];
    }
}
