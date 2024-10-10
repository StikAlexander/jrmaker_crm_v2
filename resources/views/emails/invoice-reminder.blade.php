<!DOCTYPE html>
<html>
<head>
    <title>Recordatorio de Factura</title>
</head>
<body>
    <h1>{{ $subject }}</h1>

    <p>Estimado {{ $invoice->client->name }},</p>

    <p>Le recordamos que su factura #{{ $invoice->invoice_number }} con un monto de ${{ $invoice->total_amount }} tiene una fecha de vencimiento de {{ $invoice->due_date->format('d-m-Y') }}.</p>

    @if ($invoice->due_date < now())
        <p>La factura ya ha vencido, le rogamos realizar el pago lo antes posible para evitar recargos adicionales.</p>
    @else
        <p>Le recomendamos realizar el pago antes de la fecha de vencimiento para evitar retrasos.</p>
    @endif

    <p>Gracias por su atención.</p>
</body>
</html>
