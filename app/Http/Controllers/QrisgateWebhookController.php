<?php

namespace App\Http\Controllers;

use App\Services\QrisgatePaymentSync;
use App\Services\QrisgateService;
use Illuminate\Http\Request;

class QrisgateWebhookController extends Controller
{
    public function __invoke(Request $request, QrisgateService $qrisgate, QrisgatePaymentSync $sync)
    {
        $raw = $request->getContent();

        if (! $qrisgate->verifyWebhookSignature($raw, $request->header('X-QRISGate-Signature'), $request->header('X-QRISGate-Timestamp'))) {
            \Log::warning('[QRISGate] Webhook rejected: invalid signature', ['ip' => $request->ip()]);

            return response()->json(['received' => false, 'message' => 'Invalid signature'], 401);
        }

        $payload = json_decode($raw, true);

        if (! is_array($payload)) {
            return response()->json(['received' => false, 'message' => 'Invalid payload'], 400);
        }

        $event = $payload['event'] ?? $request->header('X-QRISGate-Event');
        $status = QrisgatePaymentSync::statusFromEvent($event);
        $payment = $sync->findPayment($payload['transaction_id'] ?? null, $payload['external_order_id'] ?? null);

        if (! $status || ! $payment) {
            \Log::info('[QRISGate] Webhook ignored', [
                'event' => $event,
                'transaction_id' => $payload['transaction_id'] ?? null,
                'external_order_id' => $payload['external_order_id'] ?? null,
                'delivery_id' => $request->header('X-QRISGate-Delivery-Id'),
            ]);

            return response()->json(['received' => true]);
        }

        $payload['delivery_id'] = $request->header('X-QRISGate-Delivery-Id');
        $payload['attempt'] = $request->header('X-QRISGate-Attempt');

        $sync->apply($payment, $status, $payload, 'webhook');

        return response()->json(['received' => true]);
    }
}
