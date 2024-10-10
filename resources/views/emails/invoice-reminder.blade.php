@component('mail::message')
# {{ $subject }}

Estimado {{ $invoice->client->name }},

Te recordamos que la factura **#{{ $invoice->invoice_number }}** con un monto de **${{ number_format($invoice->total_amount, 2) }}** tiene la siguiente situación:

@if ($invoice->due_date < now())
- **Fecha de vencimiento**: {{ $invoice->due_date->format('d-m-Y') }} (¡Ya vencida!)
@else
- **Fecha de vencimiento**: {{ $invoice->due_date->format('d-m-Y') }}
@endif

Por favor, asegúrate de realizar el pago lo antes posible para evitar problemas adicionales.

Gracias por tu atención,

{{ config('app.name') }}
@endcomponent
