<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Helvetica, sans-serif;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid black;
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
    <title>Facturas Próximas a Vencer</title>
</head>
<body>
    <h2 class="text-center">Reporte de Facturas Próximas a Vencer</h2>
    <table>
        <thead>
            <tr>
                <th>Número de Factura</th>
                <th>Cliente</th>
                <th>Fecha de Vencimiento</th>
                <th>Monto Pendiente</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->client->name }}</td>
                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                    <td>${{ number_format($invoice->pending_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
