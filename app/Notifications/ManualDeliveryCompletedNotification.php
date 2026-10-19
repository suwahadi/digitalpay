<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ManualDeliveryCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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
        
        $orderData = $this->prepareOrderData();
        
        $result = $brevoService->sendManualDeliveryCompleted($orderData);
        
        return $result['success'] ?? false;
    }

    /**
     * Prepare order data for email
     */
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
            'order_date' => $this->order->created_at->format('d M Y H:i'),
            'payment_date' => $this->order->midtrans_paid_at?->format('d M Y H:i') ?? now()->format('d M Y H:i'),
            'delivery_date' => now()->format('d M Y H:i'),
            'items' => $this->order->items
                ->filter(function ($item) {
                    // Only include manual delivery items that have delivery_content
                    $delivery = $item->digitalDelivery;
                    return $delivery 
                        && $delivery->delivery_trigger === 'manual' 
                        && $delivery->delivery_content;
                })
                ->map(function ($item) {
                    $delivery = $item->digitalDelivery;
                    
                    return [
                        'product_name' => $item->product_name,
                        'variant_name' => $item->variant_name,
                        'quantity' => $item->quantity,
                        'formatted_total' => 'Rp ' . number_format($item->quantity * $item->price, 0, ',', '.'),
                        'delivery_content' => $delivery->delivery_content,
                        'instructions' => $item->productVariant?->instructions ?? null,
                    ];
                })->toArray(),
        ];
    }
}
