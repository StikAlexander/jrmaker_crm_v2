<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentsSeeder extends Seeder
{
    public function run()
    {
        $invoices = DB::table('invoices')->get();
        $paymentNumber = 1;  // Variable en minúsculas para consistencia

        while ($invoices->isNotEmpty()) {
            $numberOfInvoices = rand(1, 3);
            $selectedInvoices = $invoices->splice(0, $numberOfInvoices);

            $clientId = $selectedInvoices->first()->client_id;
            $createdBy = $clientId;
            $totalAmount = $selectedInvoices->sum('total_amount');
            $paymentDate = Carbon::parse($selectedInvoices->first()->issue_date)->addDays(rand(1, 30));

            // Inserción en la tabla 'payments' 
            $paymentId = DB::table('payments')->insertGetId([
                'payment_number' => $paymentNumber,
                'issue_date' => $selectedInvoices->first()->issue_date,
                'due_date' => $selectedInvoices->first()->due_date,
                'client_id' => $clientId,
                'payment_date' => $paymentDate,
                'amount' => $totalAmount,
                'payment_link' => null,  // Inicialmente null hasta que se genere el link
                'payment_status' => 'Pending',  // Estado inicial del pago
                'api_response' => null,  // Respuesta de la API aún no disponible
                'external_reference' => 'ref_' . uniqid(),  // Generar external_reference único
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Inserción en la tabla pivot 'payment_invoice'
            foreach ($selectedInvoices as $invoice) {
                DB::table('payment_invoice')->insert([
                    'payment_id' => $paymentId,
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->total_amount,
                ]);
            }

            $paymentNumber++;
        }
    }
}
