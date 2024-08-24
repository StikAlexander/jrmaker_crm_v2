<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvoicesSeeder extends Seeder
{
    public function run()
    {
        $clientIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'client')
            ->pluck('users.id')
            ->toArray();

        $invoiceNumber = 501;
        $invoices = [];

        foreach ($clientIds as $clientId) {
            for ($i = 0; $i < rand(5, 10); $i++) {
                $invoices[] = $this->generateInvoice($clientId, $invoiceNumber++);
            }
        }

        DB::table('invoices')->insert($invoices);
    }

    private function generateInvoice($clientId, $invoiceNumber)
    {
        $createdBy = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['super_admin', 'admin', 'collaborator'])
            ->pluck('users.id')
            ->random();

        $issueDate = Carbon::now()->subMonths(rand(0, 12))->subDays(rand(0, 30));
        $dueDate = (clone $issueDate)->addDays(rand(15, 60));
        $totalAmount = rand(500, 5000);
        $totalPaid = rand(0, $totalAmount);

        return [
            'invoice_number' => $invoiceNumber,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'total_amount' => $totalAmount,
            'client_id' => $clientId,
            'created_by' => $createdBy,
            'status' => $this->randomStatus($totalAmount, $totalPaid),
            'pending_amount' => $totalAmount - $totalPaid,
            'total_paid' => $totalPaid,
            'invoice_pdf' => 'invoices/' . $invoiceNumber . '.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function randomStatus($totalAmount, $totalPaid)
    {
        if ($totalPaid === 0) {
            return 'Pending';
        } elseif ($totalPaid < $totalAmount) {
            return 'Pending'; // Cambiado a "Pending" o "Paid" en lugar de "Partially Paid"
        } else {
            return 'Paid';
        }
    }
}
