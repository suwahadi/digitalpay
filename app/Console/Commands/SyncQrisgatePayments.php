<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use App\Services\QrisgateException;
use App\Services\QrisgatePaymentSync;
use App\Services\QrisgateService;
use Illuminate\Console\Command;

class SyncQrisgatePayments extends Command
{
    protected $signature = 'qrisgate:sync {--limit=50}';

    protected $description = 'Check pending QRISGate transactions in case a webhook was missed';

    public function handle(QrisgateService $qrisgate, QrisgatePaymentSync $sync): int
    {
        if (! config('services.qrisgate.api_key')) {
            return self::SUCCESS;
        }

        $payments = Payment::with('order')
            ->where('gateway_provider', Order::QRISGATE)
            ->whereNotNull('gateway_transaction_id')
            ->where('created_at', '>=', now()->subDay())
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'paid'))
            ->where(function ($q) {
                $q->where('status', Payment::STATUS_PENDING)
                    ->orWhere(fn ($q) => $q->where('status', Payment::STATUS_EXPIRED)->where('expired_at', '>=', now()->subMinutes(30)));
            })
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        $updated = 0;

        foreach ($payments as $payment) {
            try {
                $data = $qrisgate->getTransaction($payment->gateway_transaction_id);
            } catch (QrisgateException $e) {
                $this->warn("{$payment->gateway_transaction_id}: {$e->getMessage()}");

                continue;
            }

            $status = QrisgatePaymentSync::statusFromApi($data['status'] ?? null);

            if ($status === QrisgatePaymentSync::PAID
                || ($status !== null && $payment->status === Payment::STATUS_PENDING)) {
                $updated += (int) $sync->apply($payment, $status, $data, 'sync');
            }
        }

        $this->info("Checked {$payments->count()} QRISGate transactions, updated {$updated}.");

        return self::SUCCESS;
    }
}
