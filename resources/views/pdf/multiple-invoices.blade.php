<!-- resources/views/pdf/multiple-invoices.blade.php -->

<h1>Facturas Asociadas al Pago {{ $invoices->first()->payment->payment_number }}</h1>

@foreach ($invoices as $invoice)
    <h2>Factura #{{ $invoice->invoice_number }}</h2>
    <p>Cliente: {{ $invoice->client->name }}</p>
    <p>Fecha de Emisión: {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</p>
    <p>Monto Total: ${{ number_format($invoice->total_amount, 2) }}</p>
    <p>Monto Pagado: ${{ number_format($invoice->total_paid, 2) }}</p>
    <p>Monto Pendiente: ${{ number_format($invoice->pending_amount, 2) }}</p>
    <hr>
@endforeach
