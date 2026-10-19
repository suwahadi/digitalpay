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

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, array $deliveryData = [])
    {
        $this->order = $order;
        $this->deliveryData = $deliveryData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Only use database channel for User model, not for GuestUser
        $channels = ['brevo'];
        
        if ($notifiable instanceof User) {
            $channels[] = 'database';
        }
        
        return $channels;
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'Produk digital untuk pesanan ' . $this->order->order_number . ' telah dikirim',
        ];
    }

    /**
     * Send notification via Brevo
     */
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

    /**
     * Prepare delivery data for email
     */
    protected function prepareDeliveryData(): array
    {
        // Load relationships including digital deliveries
        $this->order->load([
            'items.productVariant.product',
            'items.digitalDelivery'
        ]);
        
        return [
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer_name,
            'customer_email' => $this->order->customer_email,
            'order_date' => $this->order->created_at->format('d M Y H:i'),
            'delivery_date' => now()->format('d M Y H:i'),
            'items' => $this->order->items
                ->filter(function ($item) {
                    // Include all items with digital products
                    return $item->productVariant?->product?->is_digital ?? false;
                })
                ->map(function ($item) {
                    // Get the actual delivery content from DigitalDelivery
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

    /**
     * Mark auto-triggered digital deliveries as having their email sent
     */
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
