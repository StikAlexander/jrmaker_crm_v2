<h1>Recordatorio de facturas vencidas</h1>

<p>Estimado cliente, estas son las facturas que tiene pendientes:</p>

<ul>
    @foreach ($invoices as $invoice)
        <li>Factura #{{ $invoice->invoice_number }} - Monto: ${{ $invoice->total_amount }} - Vence: {{ $invoice->due_date->format('d-m-Y') }}</li>
    @endforeach
</ul>

<p>Por favor, realice el pago a la mayor brevedad posible.</p>
