<?php

namespace App\Jobs;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\DigitalDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessDigitalDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    public $backoff = [10, 30];

    protected Order $order;
    protected string $trigger;
    protected array $options;

    public function __construct(Order $order, string $trigger = 'auto', array $options = [])
    {
        $this->order = $order;
        $this->trigger = $trigger;
        $this->options = $options;
    }

    public function handle(DigitalDeliveryService $deliveryService): void
    {
        if ($this->trigger === 'manual') {
            $deliveryService->processManualDelivery(
                $this->order,
                $this->options['user_id'] ?? null,
                $this->options['notes'] ?? null
            );

            return;
        }

        if ($deliveryService->orderHasDigitalProducts($this->order)) {
            $deliveryService->processOrderDigitalDelivery($this->order);
        }

        app(OrderObserver::class)->notifyAdminPaymentReceived($this->order->fresh());
    }

    public function failed(?Throwable $exception = null): void
    {
        if ($this->trigger === 'manual') {
            return;
        }

        $order = $this->order->fresh();

        if (! $order || $order->status !== 'paid') {
            return;
        }

        $hasUndelivered = $order->orderItems()->where('delivery_status', '!=', 'delivered')->exists();

        if ($hasUndelivered && ! $order->needs_manual_delivery) {
            $order->markAsManualDeliveryQueued();
        }

        app(OrderObserver::class)->notifyAdminPaymentReceived($order->fresh());
    }
}
