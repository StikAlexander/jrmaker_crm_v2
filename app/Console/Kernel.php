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
            // Primer recordatorio: 7 días antes del vencimiento
            $invoicesForFirstReminder = \App\Models\Invoice::where('status', 'Pending')
                ->where('due_date', '=', now()->addDays(7)) // Factura vence en 7 días
                ->where(function ($query) {
                    $query->whereNull('last_reminder_sent_at') // Nunca se ha enviado un recordatorio
                          ->orWhere('last_reminder_sent_at', '<=', now()->subDays(7)); // Último recordatorio hace más de 7 días
                })
                ->get();
    
            Log::info('Número de facturas encontradas para el primer recordatorio: ' . $invoicesForFirstReminder->count());
    
            foreach ($invoicesForFirstReminder as $invoice) {
                Log::info('Enviando primer recordatorio para la factura: ' . $invoice->invoice_number);
                
                Mail::to($invoice->client->email)
                    ->send(new \App\Mail\InvoiceReminder($invoice, 'Recordatorio: Su factura está próxima a vencer'));
                
                $invoice->update(['last_reminder_sent_at' => now()]); // Actualizar última fecha de recordatorio
            }
    
            // Segundo recordatorio: 3 días después del vencimiento
            $invoicesForSecondReminder = \App\Models\Invoice::where('status', 'Pending')
                ->where('due_date', '=', now()->subDays(3)) // Factura vencida hace 3 días
                ->where(function ($query) {
                    $query->whereNull('last_reminder_sent_at') // Nunca se ha enviado un recordatorio
                          ->orWhere('last_reminder_sent_at', '<=', now()->subDays(3)); // Último recordatorio hace más de 3 días
                })
                ->get();
    
            Log::info('Número de facturas encontradas para el segundo recordatorio: ' . $invoicesForSecondReminder->count());
    
            foreach ($invoicesForSecondReminder as $invoice) {
                Log::info('Enviando segundo recordatorio para la factura: ' . $invoice->invoice_number);
                
                Mail::to($invoice->client->email)
                    ->send(new \App\Mail\InvoiceReminder($invoice, 'Recordatorio: Su factura ya ha vencido, por favor pague'));
                
                $invoice->update(['last_reminder_sent_at' => now()]); // Actualizar última fecha de recordatorio
            }
    
            // Tercer recordatorio: 7 días después del segundo recordatorio
            $invoicesForThirdReminder = \App\Models\Invoice::where('status', 'Pending')
                ->where('due_date', '<', now()->subDays(10)) // Factura vencida hace 10 días (7 + 3 días)
                ->where(function ($query) {
                    $query->whereNull('last_reminder_sent_at') // Nunca se ha enviado un recordatorio
                          ->orWhere('last_reminder_sent_at', '<=', now()->subDays(7)); // Último recordatorio hace más de 7 días
                })
                ->get();
    
            Log::info('Número de facturas encontradas para el tercer recordatorio: ' . $invoicesForThirdReminder->count());
    
            foreach ($invoicesForThirdReminder as $invoice) {
                Log::info('Enviando tercer recordatorio para la factura: ' . $invoice->invoice_number);
            
                // Encolar el envío del correo en lugar de hacerlo inmediatamente
                Mail::to($invoice->client->email)
                    ->queue(new \App\Mail\InvoiceReminder($invoice, 'URGENTE: Su factura lleva más de una semana vencida, por favor agradeceriamos su pago si tiene algun inconveniente contactar a stik gamboa numero 3103292291'));
            
                $invoice->update(['last_reminder_sent_at' => now()]); // Actualizar última fecha de recordatorio
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
