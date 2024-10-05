<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Jobs\CheckPaymentStatus;
use App\Models\Payment;

class JobSeeder extends Seeder
{
    public function run()
    {
        $payment = Payment::first(); // Ejemplo, cambia según tu necesidad
        
    }
}