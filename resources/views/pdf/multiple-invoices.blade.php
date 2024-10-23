<!-- resources/views/pdf/multiple-invoices.blade.php -->

<h1>Facturas Asociadas al Pago</h1>

@foreach ($invoices as $invoice)
    <h2>Factura #{{ $invoice->invoice_number ?? 'N/A' }}</h2>
    <p>Cliente: {{ $invoice->client->name ?? 'N/A' }}</p>
    <p>Fecha de Emisión: {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</p>
    <p>Monto Total: ${{ $invoice->total_amount !== null ? number_format($invoice->total_amount, 2) : 'N/A' }}</p>
    <p>Monto Pagado: ${{ $invoice->total_paid !== null ? number_format($invoice->total_paid, 2) : 'N/A' }}</p>
    <p>Monto Pendiente: ${{ $invoice->pending_amount !== null ? number_format($invoice->pending_amount, 2) : 'N/A' }}</p>

    <!-- Iterar sobre los pagos asociados a la factura -->
    @foreach ($invoice->payments as $payment)
        <p>Número de Pago: {{ $payment->payment_number ?? 'N/A' }}</p>
        <p>Monto Pagado para esta factura: ${{ $payment->pivot->amount !== null ? number_format($payment->pivot->amount, 2) : 'N/A' }}</p>
    @endforeach

    <hr>
@endforeach
