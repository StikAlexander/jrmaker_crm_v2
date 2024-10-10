<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            // Obtener las facturas pendientes y agruparlas por cliente
            $invoicesByClient = \App\Models\Invoice::where('status', 'Pending')
                ->where('due_date', '<', now()->subDays(10)) // Factura vencida hace 10 días (7 + 3 días)
                ->where(function ($query) {
                    $query->whereNull('last_reminder_sent_at') // Nunca se ha enviado un recordatorio
                          ->orWhere('last_reminder_sent_at', '<=', now()->subDays(7)); // Último recordatorio hace más de 7 días
                })
                ->get()
                ->groupBy('client_id'); // Agrupar facturas por cliente

            Log::info('Número de clientes con facturas pendientes: ' . $invoicesByClient->count());

            // Contador para gestionar el delay manualmente
            $counter = 0;

            foreach ($invoicesByClient as $clientId => $invoices) {
                $client = $invoices->first()->client; // Obtener información del cliente
                $invoiceNumbers = $invoices->pluck('invoice_number'); // Listar números de facturas

                Log::info('Enviando recordatorio al cliente: ' . $client->email . ' por las facturas: ' . implode(',', $invoiceNumbers->toArray()));

                // Enviar el correo con un delay de 10 segundos entre cada cliente para evitar sobrecarga
                Mail::to($client->email)
                    ->later(now()->addSeconds($counter * 10), new \App\Mail\InvoiceReminder($invoices, 'URGENTE: Su factura lleva más de una semana vencida, por favor agradeceriamos su pago.'));

                // Actualizar la fecha de último recordatorio para cada factura
                foreach ($invoices as $invoice) {
                    $invoice->last_reminder_sent_at = now();  // Actualizamos directamente la propiedad
                    $invoice->save();  // Guardamos el modelo para persistir los cambios
                }
                

                // Incrementar el contador para añadir más delay al siguiente cliente
                $counter++;
            }
        })->everyMinute(); // Ejecutar cada minuto para pruebas
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
