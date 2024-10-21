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

    <!-- Mostrar rango de fechas si existe -->
    @if(isset($fechaInicio) && isset($fechaFin))
        <p class="text-center">
            Facturas vencidas desde {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} 
            hasta {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}.
        </p>
    @else
        <p class="text-center">Facturas vencidas hasta la fecha de generación del reporte ({{ \Carbon\Carbon::now()->format('d/m/Y') }}).</p>
    @endif

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
