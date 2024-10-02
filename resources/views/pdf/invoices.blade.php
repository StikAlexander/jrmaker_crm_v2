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
        .logo {
            width: 150px;
            height: auto;
        }
    </style>
    <title>Facturas Vencidas</title>
</head>
<body>

    <table>
        <tr>
            <td><img src="{{ url('images/jr-logo-completo.jpg') }}" alt="Logo JR Maker" class="logo"></td>
            <td class="text-right">
                JR Maker SAS <br>
                Dirección de la empresa <br>
                Ciudad, País
            </td>
        </tr>
    </table>

    <h2 class="text-center">Reporte de Facturas Vencidas</h2>
    <table>
        <thead>
            <tr>
                <th>Número de Factura</th>
                <th>Cliente</th>
                <th>Fecha de Vencimiento</th>
                <th>Monto Total</th>
                <th>Monto Pendiente</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->client->name }}</td>
                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                    <td>${{ number_format($invoice->total_amount, 2) }}</td>
                    <td>${{ number_format($invoice->pending_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <p class="text-right">
        Total Facturas Vencidas: ${{ number_format($total_pending, 2) }}
    </p>
</body>
</html>
