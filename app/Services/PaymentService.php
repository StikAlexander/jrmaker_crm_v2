<?php

namespace App\Services;

use GuzzleHttp\Client;

class PaymentService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(); // Inicializa el cliente HTTP
    }

    public function generatePaymentLink($amount, $description, $clientId, $callbackUrl)
    {
        try {
            $response = $this->client->post('https://api.paymentprovider.com/create-link', [
                'json' => [
                    'amount' => $amount,
                    'description' => $description,
                    'client_id' => $clientId,
                    'callback_url' => $callbackUrl,
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['payment_link'];
        } catch (\Exception $e) {
            // Maneja los errores de la API
            \Log::error('Error generating payment link: ' . $e->getMessage());
            return null;
        }
    }
}
