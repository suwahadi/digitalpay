<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class DigitalDeliveryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;
    protected array $deliveryData;

    public function __construct(Order $order, array $deliveryData = [])
    {
        $this->order = $order;
        $this->deliveryData = $deliveryData;
    }

    public function via(object $notifiable): array
    {
        $channels = ['brevo'];

        if ($notifiable instanceof User) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'Produk digital untuk pesanan ' . $this->order->order_number . ' telah dikirim',
        ];
    }

    public function toBrevo(object $notifiable): bool
    {
        $brevoService = app(BrevoService::class);

        $deliveryData = $this->prepareDeliveryData();

        $result = $brevoService->sendDigitalDelivery($deliveryData);

        if ($result['success'] ?? false) {
            $this->markDeliveriesAsEmailed();
        }

        return $result['success'] ?? false;
    }

    protected function prepareDeliveryData(): array
    {
        $this->order->load([
            'items.productVariant.product',
            'items.digitalDelivery'
        ]);

        return [
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer_name,
            'customer_email' => $this->order->customer_email,
            'order_date' => $this->order->created_at->translatedFormat('d M Y H:i'),
            'delivery_date' => now()->translatedFormat('d M Y H:i'),
            'items' => $this->order->items
                ->filter(function ($item) {
                    return $item->digitalDelivery !== null || (bool) $item->productVariant?->isAutoDelivery();
                })
                ->map(function ($item) {
                    $digitalCode = 'Akan dikirim terpisah';
                    if ($item->digitalDelivery && $item->digitalDelivery->delivery_content) {
                        $digitalCode = $item->digitalDelivery->delivery_content;
                    }

                    return [
                        'product_name' => $item->product_name,
                        'variant_name' => $item->variant_name,
                        'quantity' => $item->quantity,
                        'digital_code' => $digitalCode,
                        'instructions' => $item->productVariant?->product?->delivery_instructions ?? '',
                    ];
                })->toArray(),
            'delivery_items' => $this->deliveryData['items'] ?? [],
            'instructions' => $this->deliveryData['instructions'] ?? 'Silakan gunakan kode digital yang telah dikirimkan.',
        ];
    }

    protected function markDeliveriesAsEmailed(): void
    {
        $this->order->loadMissing(['items.digitalDelivery']);

        $timestamp = now();

        foreach ($this->order->items as $item) {
            $delivery = $item->digitalDelivery;

            if ($delivery && $delivery->delivery_trigger === 'auto' && !$delivery->email_sent) {
                $delivery->forceFill([
                    'email_sent' => true,
                    'email_sent_at' => $timestamp,
                ])->saveQuietly();
            }
        }
    }
}
