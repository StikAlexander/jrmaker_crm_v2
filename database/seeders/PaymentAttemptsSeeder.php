<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentAttemptsSeeder extends Seeder
{
    public function run()
    {
        $voucherPayments = DB::table('voucher_payments')->get();

        foreach ($voucherPayments as $voucherPayment) {
            $numberOfAttempts = rand(1, 3);

            for ($i = 0; $i < $numberOfAttempts; $i++) {
                DB::table('payment_attempts')->insert([
                    'voucher_payment_id' => $voucherPayment->id,
                    'status' => $this->randomStatus(),
                    'response' => json_encode($this->mockApiResponse()),
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }

    private function randomStatus()
    {
        $statuses = ['Pending', 'Completed', 'Failed'];
        return $statuses[array_rand($statuses)];
    }

    private function mockApiResponse()
    {
        return [
            'message' => 'This is a mock API response.',
            'transaction_id' => rand(1000, 9999),
            'payment_link' => 'https://payment-gateway.com/mock-link',
        ];
    }
}
