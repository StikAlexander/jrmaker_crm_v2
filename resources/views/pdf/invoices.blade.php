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
            <td><img src="{{ url('storage/images/jr_maker_logo.png') }}" alt="Logo JR Maker" class="logo"></td>
            <td class="text-right">
                JR Maker SAS <br>
                Dirección de la empresa <br>
                Ciudad, País
            </td>
        </tr>
    </table>

    <h2 class="text-center">Reporte de Facturas Vencidas</h2>

    <!-- Mostrar el rango de fechas o la fecha de generación del reporte -->
    @if(isset($fechaInicio) && isset($fechaFin))
        <p class="text-center">
            Facturas vencidas desde {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} 
            hasta {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}.
        </p>
    @else
        <p class="text-center">Facturas vencidas hasta la fecha de generación del reporte ({{ \Carbon\Carbon::now()->format('d/m/Y') }}).</p>
    @endif

    <!-- Tabla de facturas vencidas -->
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
            @forelse ($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->client->name }}</td>
                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                    <td>${{ number_format($invoice->total_amount, 2) }}</td>
                    <td>${{ number_format($invoice->pending_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No hay facturas vencidas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Mostrar el total pendiente solo si hay facturas -->
    @if($invoices->isNotEmpty())
        <p class="text-right">
            Total Facturas Vencidas: ${{ number_format($total_pending, 2) }}
        </p>
    @endif
</body>
</html>
