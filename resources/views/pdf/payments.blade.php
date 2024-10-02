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
    <title>Pagos Exitosos</title>
</head>
<body>
    <h2 class="text-center">Reporte de Pagos Exitosos</h2>
    <table>
        <thead>
            <tr>
                <th>Número de Pago</th>
                <th>Cliente</th>
                <th>Fecha de Pago</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->Payment_number }}</td>
                    <td>{{ $payment->client->name }}</td>
                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                    <td>${{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <p class="text-right">
        Total Pagado: ${{ number_format($total_paid, 2) }}
    </p>
</body>
</html>
