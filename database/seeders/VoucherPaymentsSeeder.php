<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherPaymentsSeeder extends Seeder
{
    public function run()
    {
        $invoices = DB::table('invoices')->get();
        $voucherNumber = 1;

        while ($invoices->isNotEmpty()) {
            $numberOfInvoices = rand(1, 3);
            $selectedInvoices = $invoices->splice(0, $numberOfInvoices);

            $clientId = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'client')
                ->where('users.id', $selectedInvoices->first()->client_id)
                ->value('users.id');

            $createdBy = $clientId;
            $totalAmount = $selectedInvoices->sum('total_amount');
            $paymentDate = Carbon::parse($selectedInvoices->first()->issue_date)->addDays(rand(1, 30));
            $confirmationStatus = $this->randomConfirmationStatus(); // Estado de confirmación aleatorio

            $voucherPaymentId = DB::table('voucher_payments')->insertGetId([
                'voucher_number' => $voucherNumber,
                'issue_date' => $selectedInvoices->first()->issue_date,
                'due_date' => $selectedInvoices->first()->due_date,
                'client_id' => $clientId,
                'created_by' => $createdBy,
                'payment_date' => $paymentDate,
                'amount' => $totalAmount,
                'confirmation_status' => $confirmationStatus, // Confirmación
                'payment_support' => 'voucher_payments/' . $voucherNumber . '.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($selectedInvoices as $invoice) {
                DB::table('voucher_payment_invoice')->insert([
                    'voucher_payment_id' => $voucherPaymentId,
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->total_amount,
                ]);
            }

            $voucherNumber++;
        }
    }

    private function randomConfirmationStatus()
    {
        $statuses = ['Pending', 'Approved', 'Rejected'];
        return $statuses[array_rand($statuses)];
    }
}
