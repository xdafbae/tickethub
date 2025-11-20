<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class MidtransGateway
{
    public function createSnapTransaction(array $payload, string $serverKey, bool $isProduction = false): array
    {
        $baseUrl = $isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
        $url = $baseUrl . '/snap/v1/transactions';

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Midtrans error: ' . $response->body());
        }

        $data = $response->json();
        return [
            'token' => $data['token'] ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
            'raw' => $data,
        ];
    }

    public static function buildPayload(
        string $orderId,
        int $grossAmount,
        array $customerDetails,
        array $itemDetails
    ): array {
        return [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
            // Optional: callbacks URLs bisa ditambahkan di sini jika diinginkan
        ];
    }
}