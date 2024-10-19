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
    <title>Pagos Cancelados</title>
</head>
<body>

    <table>
        <tr>
            <td><img src="{{ url('storage/images/jr_maker_logo.png') }}" alt="Logo JR Maker" class="logo"></td>
            <td class="text-right">
                JR Maker SAS <br>
                Dirección de la empresa <br>
                Ciudad, País
            </td>
        </tr>
    </table>

    <h2 class="text-center">Reporte de Pagos Cancelados</h2>
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
                    <td>{{ $payment->created_at ? $payment->created_at->format('d/m/Y') : 'No disponible' }}</td>
                    <td>${{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
