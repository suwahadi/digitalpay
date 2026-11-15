<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class QrisgateService
{
    public function createTransaction(Order $order): array
    {
        $payload = [
            'external_order_id' => $order->order_number,
            'amount' => (int) round((float) $order->total_amount),
            'customer_name' => $order->customer_name,
            'return_url' => route('invoice.show', $order->order_number),
            'payment_method' => 'QRIS',
        ];

        $data = $this->send(fn (PendingRequest $http) => $http->post('/transactions', $payload));

        foreach (['transaction_id', 'total_amount', 'qr_string'] as $field) {
            if (empty($data[$field])) {
                throw new QrisgateException("QRISGate response missing {$field}");
            }
        }

        return $data;
    }

    public function getTransaction(string $transactionId): array
    {
        return $this->send(fn (PendingRequest $http) => $http->get('/transactions/'.rawurlencode($transactionId)));
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature, ?string $timestamp): bool
    {
        $secret = (string) config('services.qrisgate.webhook_secret');

        if ($secret === '' || ! $signature || ! $timestamp || ! ctype_digit($timestamp)) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > (int) config('services.qrisgate.signature_tolerance', 300)) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);
        $given = strtolower(preg_replace('/^sha256=/i', '', trim($signature)));

        return hash_equals($expected, $given);
    }

    private function send(callable $request): array
    {
        $apiKey = (string) config('services.qrisgate.api_key');

        if ($apiKey === '') {
            throw new QrisgateException('QRISGate API key is not configured');
        }

        $http = Http::baseUrl(config('services.qrisgate.base_url'))
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.qrisgate.timeout', 15));

        try {
            $response = $request($http);
        } catch (ConnectionException $e) {
            throw new QrisgateException('QRISGate connection failed: '.$e->getMessage(), 0, $e);
        }

        $json = $response->json();

        if ($response->failed() || ! is_array($json) || ($json['success'] ?? true) === false || ! is_array($json['data'] ?? null)) {
            $message = is_array($json) ? ($json['error'] ?? $json['message'] ?? null) : null;

            throw new QrisgateException(sprintf(
                'QRISGate request failed (HTTP %d)%s',
                $response->status(),
                is_string($message) ? ': '.$message : ''
            ));
        }

        return $json['data'];
    }
}
