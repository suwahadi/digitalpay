<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderCancellationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;
    protected string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $reason = 'Pembatalan pesanan')
    {
        $this->order = $order;
        $this->reason = $reason;
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
            'message' => 'Pesanan ' . $this->order->order_number . ' telah dibatalkan',
            'reason' => $this->reason,
        ];
    }

    /**
     * Send notification via Brevo
     */
    public function toBrevo(object $notifiable): bool
    {
        $brevoService = app(BrevoService::class);
        
        $orderData = $this->prepareOrderData();
        
        $result = $brevoService->sendOrderCancellation($orderData);
        
        return $result['success'] ?? false;
    }

    /**
     * Prepare order data for email
     */
    protected function prepareOrderData(): array
    {
        $this->order->load('items.productVariant.product');
        
        return [
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer_name,
            'customer_email' => $this->order->customer_email,
            'order_date' => $this->order->created_at->translatedFormat('d M Y H:i'),
            'cancellation_date' => now()->translatedFormat('d M Y H:i'),
            'reason' => $this->reason,
            'items' => $this->order->items->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'formatted_total' => 'Rp ' . number_format($item->quantity * $item->price, 0, ',', '.'),
                ];
            })->toArray(),
            'formatted_total_amount' => $this->order->formatted_total_amount,
        ];
    }
}
