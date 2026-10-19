<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderCreatedNotification extends Notification implements ShouldQueue
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
            'total_amount' => $this->order->total_amount,
            'message' => 'Pesanan baru telah dibuat dengan nomor ' . $this->order->order_number,
        ];
    }

    /**
     * Send notification via Brevo
     */
    public function toBrevo(object $notifiable): bool
    {
        $brevoService = app(BrevoService::class);
        
        $orderData = $this->prepareOrderData();
        
        $result = $brevoService->sendOrderConfirmation($orderData);
        
        return $result['success'] ?? false;
    }

    /**
     * Prepare order data for email
     */
    protected function prepareOrderData(): array
    {
        $this->order->load('items.productVariant.product', 'paymentMethod');
        
        return [
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer_name,
            'customer_email' => $this->order->customer_email,
            'customer_phone' => $this->order->customer_phone,
            'order_date' => $this->order->created_at->format('d M Y H:i'),
            'items' => $this->order->items->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->quantity * $item->price,
                    'formatted_price' => 'Rp ' . number_format($item->price, 0, ',', '.'),
                    'formatted_total' => 'Rp ' . number_format($item->quantity * $item->price, 0, ',', '.'),
                ];
            })->toArray(),
            'subtotal' => $this->order->subtotal,
            'fee_amount' => $this->order->fee_amount,
            'total_amount' => $this->order->total_amount,
            'formatted_subtotal' => $this->order->formatted_subtotal,
            'formatted_fee_amount' => $this->order->formatted_fee_amount,
            'formatted_total_amount' => $this->order->formatted_total_amount,
            'payment_method' => $this->order->paymentMethod->name ?? 'N/A',
            'expires_at' => $this->order->expires_at->format('d M Y H:i'),
            'time_remaining' => $this->order->time_remaining,
            'payment_url' => $this->order->payment?->getMidtransPaymentUrl(),
        ];
    }
}
