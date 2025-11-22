<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class MidtransGateway
{
    public function createSnapTransaction(array $payload, string $serverKey, bool $isProduction = false): array
    {
        $baseUrl = $isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
        $url = $baseUrl . '/snap/v1/transactions';

        $options = [];
        $verifySsl = (bool)config('services.midtrans.verify_ssl', true);
        $cacertConfig = config('services.midtrans.cacert_path');
        $cacert = is_string($cacertConfig) ? (realpath($cacertConfig) ?: $cacertConfig) : null;
        $caDir = (is_string($cacert) && strpos($cacert, DIRECTORY_SEPARATOR) !== false) ? dirname($cacert) : null;

        if ($verifySsl === false) {
            $options['verify'] = false;
        } else {
            if (is_string($cacert) && is_file($cacert) && is_readable($cacert)) {
                // Pakai bundle PEM spesifik dan set opsi cURL agar Windows membaca file
                $options['verify'] = $cacert;
                $options['curl'] = [
                    \CURLOPT_CAINFO => $cacert,
                    \CURLOPT_CAPATH => $caDir ?? '',
                    \CURLOPT_SSL_VERIFYPEER => true,
                    \CURLOPT_SSL_VERIFYHOST => 2,
                ];
            } else {
                // Fallback ke CA sistem
                $options['verify'] = true;
            }
        }

        $response = Http::withOptions($options)
            ->withBasicAuth($serverKey, '')
            ->acceptJson()
            ->asJson() // pastikan payload dikirim sebagai JSON
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException(sprintf(
                'Midtrans error (HTTP %d): %s',
                $response->status(),
                $response->body()
            ));
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