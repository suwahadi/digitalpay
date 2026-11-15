<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Support\Carbon;

class QrisgatePaymentSync
{
    public const PAID = 'paid';

    public const EXPIRED = 'expired';

    public const CANCELLED = 'cancelled';

    public function findPayment(?string $transactionId, ?string $orderNumber): ?Payment
    {
        $query = Payment::with('order')->where('gateway_provider', Order::QRISGATE);

        if ($transactionId) {
            $payment = (clone $query)->where('gateway_transaction_id', $transactionId)->latest('id')->first();

            if ($payment) {
                return $payment;
            }
        }

        if (! $orderNumber) {
            return null;
        }

        return $query->whereHas('order', fn ($q) => $q->where('order_number', $orderNumber))->latest('id')->first();
    }

    public function markPaidByAdmin(Order $order, ?int $userId, ?string $userName): bool
    {
        if (! $order->isQrisgate() || $order->status === 'paid') {
            return false;
        }

        $payment = $order->payments()->where('gateway_provider', Order::QRISGATE)->latest('id')->first();
        $data = ['marked_by_user_id' => $userId, 'marked_by' => $userName, 'paid_at' => now()->toIso8601String()];

        if (! $payment) {
            return $order->markAsPaidFromGateway(now(), ['triggered_by' => 'qrisgate_admin', 'user_id' => $userId]);
        }

        return $this->apply($payment->setRelation('order', $order), self::PAID, $data, 'admin');
    }

    public static function statusFromEvent(?string $event): ?string
    {
        return match ($event) {
            'payment.paid' => self::PAID,
            'payment.expired' => self::EXPIRED,
            'payment.cancelled' => self::CANCELLED,
            default => null,
        };
    }

    public static function statusFromApi(?string $status): ?string
    {
        return match (strtoupper((string) $status)) {
            'PAID' => self::PAID,
            'EXPIRED' => self::EXPIRED,
            'CANCELLED', 'CANCELED', 'FAILED' => self::CANCELLED,
            default => null,
        };
    }

    public function apply(Payment $payment, string $status, array $data, string $source): bool
    {
        $order = $payment->order;

        if (! $order || ! $order->isQrisgate()) {
            return false;
        }

        PaymentLog::create([
            'payment_id' => $payment->id,
            'event_type' => 'qrisgate_'.$source.'_'.$status,
            'request_data' => $data,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        return match ($status) {
            self::PAID => $this->markPaid($payment, $order, $data, $source),
            self::EXPIRED, self::CANCELLED => $this->close($payment, $order, $status),
            default => false,
        };
    }

    private function markPaid(Payment $payment, Order $order, array $data, string $source): bool
    {
        $paidAt = $this->parseTime($data['paid_at'] ?? null) ?? now();

        if ($payment->status !== Payment::STATUS_SUCCESS) {
            $payment->update([
                'status' => Payment::STATUS_SUCCESS,
                'paid_at' => $paidAt,
            ]);
        }

        if (isset($data['total_amount']) && (int) $data['total_amount'] !== (int) round((float) $order->total_amount)) {
            \Log::warning('[QRISGate] Paid amount differs from order total', [
                'order_number' => $order->order_number,
                'order_total' => (int) round((float) $order->total_amount),
                'paid_total' => (int) $data['total_amount'],
            ]);
        }

        return $order->markAsPaidFromGateway($paidAt, [
            'triggered_by' => 'qrisgate_'.$source,
            'transaction_id' => $payment->gateway_transaction_id,
        ]);
    }

    private function close(Payment $payment, Order $order, string $status): bool
    {
        if ($order->status === 'paid' || $payment->status === Payment::STATUS_SUCCESS) {
            return false;
        }

        $payment->update([
            'status' => $status === self::EXPIRED ? Payment::STATUS_EXPIRED : Payment::STATUS_CANCELLED,
            'expired_at' => $payment->expired_at ?? now(),
        ]);

        if (in_array($order->status, ['cancelled', 'expired'], true)) {
            return false;
        }

        $order->update(['status' => $status, 'payment_status' => $status]);
        $order->items()->whereNotIn('delivery_status', ['delivered', 'failed'])->update(['delivery_status' => 'failed']);

        return true;
    }

    private function parseTime(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->setTimezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
