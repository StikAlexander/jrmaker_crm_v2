<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        $totalAmount = rand(100000, 500000); // Ajuste de monto
        $totalPaid = rand(0, $totalAmount);
        $description = $this->generateDescription(); // Descripción aleatoria

        // Simulamos que algunas facturas no han recibido recordatorio
        $lastReminderSentAt = $this->randomLastReminder(); 

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
            'description' => $description,
            'invoice_pdf' => 'invoices/' . $invoiceNumber . '.pdf',
            'last_reminder_sent_at' => $lastReminderSentAt, // Añadimos el campo de recordatorio
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function generateDescription()
    {
        $descriptions = [
            'Instalación de vidrios templados',
            'Puertas corredizas de aluminio',
            'Marcos de ventanas de madera',
            'Reparación de cerraduras de puertas',
            'Diseño de ornamentos de hierro',
            'Instalación de mamparas de vidrio',
            'Cambio de puertas de madera',
            'Decoración de ventanales'
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function randomStatus($totalAmount, $totalPaid)
    {
        if ($totalPaid === 0) {
            return 'Pending';
        } elseif ($totalPaid < $totalAmount) {
            return 'Pending';
        } else {
            return 'Paid';
        }
    }

    // Generar valor aleatorio para el campo last_reminder_sent_at
    private function randomLastReminder()
    {
        // 50% de probabilidad de tener una fecha de recordatorio previa
        return rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null;
    }
}
