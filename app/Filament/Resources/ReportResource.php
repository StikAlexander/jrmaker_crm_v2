<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Report;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Enums\ActionSize;

class ReportResource extends Resource
{
    // Configuración del recurso
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $label = 'Generador de Reportes';
    protected static ?int $navigationSort = 10;

    protected static ?string $model = Report::class;

    // Definición del formulario (selección de reporte y fechas)
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('reporte_tipo')
                    ->label('Selecciona el tipo de reporte')
                    ->options([
                        'facturas_vencidas' => 'Facturas Vencidas',
                        'pagos_exitosos' => 'Pagos Exitosos',
                        'clientes_mayor_deuda' => 'Clientes con Mayor Deuda Pendiente',
                        'pagos_fallidos' => 'Pagos Fallidos',
                        'pagos_cancelados' => 'Pagos Cancelados',
                        'facturas_proximas_a_vencer' => 'Facturas Próximas a Vencer',
                        'pagos_facturas_vencidas' => 'Pagos de Facturas Vencidas',
                        'facturas_con_anticipo' => 'Facturas con Anticipo Recibido',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->required(),

                Forms\Components\DatePicker::make('fecha_fin')
                    ->label('Fecha de Fin')
                    ->required(),
            ]);
    }

    // Definición de la tabla y acciones
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('reporte_tipo')->label('Tipo de Reporte')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('fecha_inicio')->label('Fecha de Inicio')->date()->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')->label('Fecha de Fin')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Creado El')->dateTime()->sortable(),
            ])
            ->actions([
                Action::make('descargar_reporte')
                    ->label('Descargar Reporte')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Model $record) {
                        switch ($record->reporte_tipo) {
                            case 'facturas_vencidas':
                                return self::generarFacturasVencidas($record->fecha_inicio, $record->fecha_fin);
                            case 'pagos_exitosos':
                                return self::generarPagosExitosos($record->fecha_inicio, $record->fecha_fin);
                            case 'clientes_mayor_deuda':
                                return self::generarClientesMayorDeuda($record->fecha_inicio, $record->fecha_fin);
                            case 'pagos_fallidos':
                                return self::generarPagosFallidos($record->fecha_inicio, $record->fecha_fin);
                            case 'pagos_cancelados':
                                return self::generarPagosCancelados($record->fecha_inicio, $record->fecha_fin);
                            case 'facturas_proximas_a_vencer':
                                return self::generarFacturasProximasAVencer();
                            case 'pagos_facturas_vencidas':
                                return self::generarPagosFacturasVencidas($record->fecha_inicio, $record->fecha_fin);
                            case 'facturas_con_anticipo':
                                return self::generarFacturasConAnticipo($record->fecha_inicio, $record->fecha_fin);
                            default:
                                throw new \Exception("Tipo de reporte no válido.");
                        }
                    })
                    ->requiresConfirmation() // Opcional: solicita confirmación antes de descargar
                    ->color('success')
                    ->tooltip('Descargar este reporte'),
                    
                    Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->size(ActionSize::Large)
                    ->tooltip('Borrar Cliente')
                    ->iconButton(),
                    
            ])
            ->bulkActions([
                // Acciones masivas si es necesario
            ])
            ->filters([
                // Filtros si son necesarios
            ]);
    }

    // Métodos para generar reportes existentes
    protected static function generarFacturasVencidas($fechaInicio, $fechaFin)
    {
        $facturasVencidas = Invoice::where('due_date', '<', Carbon::now())
            ->where('status', '!=', 'Paid')
            ->whereBetween('due_date', [$fechaInicio, $fechaFin])
            ->get();

        $totalPendiente = $facturasVencidas->sum('pending_amount');

        $pdf = FacadePdf::loadView('pdf.invoices', [
            'invoices' => $facturasVencidas,
            'total_pending' => $totalPendiente,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'facturas_vencidas_' . date('d_m_Y') . '.pdf');
    }

    protected static function generarPagosExitosos($fechaInicio, $fechaFin)
    {
        $pagosExitosos = Payment::where('payment_status', 'Completed')
            ->whereBetween('payment_date', [$fechaInicio, $fechaFin])
            ->get();

        $totalPagado = $pagosExitosos->sum('amount');

        $pdf = FacadePdf::loadView('pdf.payments', [
            'payments' => $pagosExitosos,
            'total_paid' => $totalPagado,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'pagos_exitosos_' . date('d_m_Y') . '.pdf');
    }

    protected static function generarClientesMayorDeuda($fechaInicio, $fechaFin)
    {
        $clientes = Invoice::selectRaw('client_id, sum(pending_amount) as total_pendiente')
            ->where('status', 'Pending')
            ->whereBetween('due_date', [$fechaInicio, $fechaFin])
            ->groupBy('client_id')
            ->orderBy('total_pendiente', 'desc')
            ->take(10)
            ->get();

        $pdf = FacadePdf::loadView('pdf.clientes_mayor_deuda', [
            'clientes' => $clientes,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'clientes_mayor_deuda_' . date('d_m_Y') . '.pdf');
    }

    // Métodos para generar nuevos reportes
    protected static function generarPagosFallidos($fechaInicio, $fechaFin)
    {
        $pagosFallidos = Payment::where('payment_status', 'Failed')
            ->whereBetween('payment_date', [$fechaInicio, $fechaFin])
            ->get();

        $pdf = FacadePdf::loadView('pdf.payments-failed', [ // Cambiado a inglés
            'payments' => $pagosFallidos,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'pagos_fallidos_' . date('d_m_Y') . '.pdf');
    }

    protected static function generarPagosCancelados($fechaInicio, $fechaFin)
    {
        $pagosCancelados = Payment::where('payment_status', 'Cancelled')
            ->whereBetween('payment_date', [$fechaInicio, $fechaFin])
            ->get();

        $pdf = FacadePdf::loadView('pdf.payments-cancelled', [ // Cambiado a inglés
            'payments' => $pagosCancelados,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'pagos_cancelados_' . date('d_m_Y') . '.pdf');
    }

    protected static function generarFacturasProximasAVencer()
    {
        $fechaInicio = Carbon::now();
        $fechaFin = Carbon::now()->addDays(7);

        $facturas = Invoice::whereBetween('due_date', [$fechaInicio, $fechaFin])
            ->where('status', 'Pending')
            ->get();

        $pdf = FacadePdf::loadView('pdf.invoices-near-expiration', [ // Cambiado a inglés
            'invoices' => $facturas,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'facturas_proximas_vencer_' . date('d_m_Y') . '.pdf');
    }

    protected static function generarPagosFacturasVencidas($fechaInicio, $fechaFin)
    {
        $pagos = Payment::whereHas('invoice', function($query) {
                $query->where('due_date', '<', Carbon::now());
            })
            ->whereBetween('payment_date', [$fechaInicio, $fechaFin])
            ->get();

        $pdf = FacadePdf::loadView('pdf.payments-to-overdue-invoices', [ // Cambiado a inglés
            'payments' => $pagos,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'pagos_facturas_vencidas_' . date('d_m_Y') . '.pdf');
    }

    protected static function generarFacturasConAnticipo($fechaInicio, $fechaFin)
    {
        $facturasConAnticipo = Invoice::where('pending_amount', '>', 0)
            ->whereBetween('due_date', [$fechaInicio, $fechaFin])
            ->get();

        $pdf = FacadePdf::loadView('pdf.invoices-with-advance', [ // Cambiado a inglés
            'invoices' => $facturasConAnticipo,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'facturas_con_anticipo_' . date('d_m_Y') . '.pdf');
    }

    // Definición de las páginas (List, Create, Edit)
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
