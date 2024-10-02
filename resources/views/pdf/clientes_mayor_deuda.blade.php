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
    <title>Clientes con Mayor Deuda</title>
</head>
<body>
    <h2 class="text-center">Reporte de Clientes con Mayor Deuda</h2>
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Total Pendiente</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->client->name }}</td>
                    <td>${{ number_format($cliente->total_pendiente, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
