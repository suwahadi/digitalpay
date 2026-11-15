<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ManualDeliveryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
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
            'message' => 'Pesanan ' . $this->order->order_number . ' sedang diproses secara manual',
        ];
    }

    public function toBrevo(object $notifiable): bool
    {
        $brevoService = app(BrevoService::class);

        $orderData = $this->prepareOrderData();

        $result = $brevoService->sendManualDeliveryNotification($orderData);

        return $result['success'] ?? false;
    }

    protected function prepareOrderData(): array
    {
        $this->order->load([
            'items.productVariant.product',
            'items.digitalDelivery.digitalStock'
        ]);

        return [
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer_name,
            'customer_email' => $this->order->customer_email,
            'customer_phone' => $this->order->customer_phone,
            'order_date' => $this->order->created_at->translatedFormat('d M Y H:i'),
            'payment_date' => $this->order->midtrans_paid_at?->translatedFormat('d M Y H:i') ?? now()->translatedFormat('d M Y H:i'),
            'delivery_date' => now()->translatedFormat('d M Y H:i'),
            'items' => $this->order->items
                ->filter(function ($item) {
                    return (bool) $item->productVariant?->isManualDelivery();
                })
                ->map(function ($item) {
                    $delivery = $item->digitalDelivery;
                    $digitalCode = null;

                    if ($delivery && $delivery->delivery_trigger === 'manual' && $delivery->delivery_content) {
                        $digitalCode = $delivery->delivery_content;
                    } else {
                        $digitalCode = 'Produk sedang diproses...';
                    }

                    return [
                        'product_name' => $item->product_name,
                        'variant_name' => $item->variant_name,
                        'quantity' => $item->quantity,
                        'formatted_total' => 'Rp ' . number_format($item->quantity * $item->price, 0, ',', '.'),
                        'digital_code' => $digitalCode,
                        'instructions' => $item->productVariant?->instructions ?? null,
                    ];
                })->toArray(),
            'estimated_time' => '1-3 hari kerja',
            'manual_notes' => $this->order->manual_notes ?? '',
        ];
    }
}
