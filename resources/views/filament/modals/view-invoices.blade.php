<!-- resources/views/filament/modals/view-invoices.blade.php -->
<div>
    <h3>Facturas Pagadas</h3>
    <ul>
        @foreach($invoices as $invoice)
            <li>
                Factura: {{ $invoice->invoice_number }} - Monto: ${{ number_format($invoice->total_amount, 0) }} - Estado: {{ $invoice->status }}
            </li>
        @endforeach
    </ul>
</div>
