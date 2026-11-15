<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentSuccessNotification extends Notification implements ShouldQueue
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
            'total_amount' => $this->order->total_amount,
            'message' => 'Pembayaran untuk pesanan ' . $this->order->order_number . ' telah berhasil',
        ];
    }

    public function toBrevo(object $notifiable): bool
    {
        $brevoService = app(BrevoService::class);

        $orderData = $this->prepareOrderData();

        $result = $brevoService->sendPaymentSuccess($orderData);

        return $result['success'] ?? false;
    }

    protected function prepareOrderData(): array
    {
        $this->order->load('items.productVariant.product', 'paymentMethod');

        return [
            'order_number' => $this->order->order_number,
            'invoice_url' => route('invoice.show', $this->order->order_number),
            'customer_name' => $this->order->customer_name,
            'customer_email' => $this->order->customer_email,
            'customer_phone' => $this->order->customer_phone,
            'order_date' => $this->order->created_at->translatedFormat('d M Y H:i'),
            'payment_date' => $this->order->midtrans_paid_at?->translatedFormat('d M Y H:i') ?? now()->translatedFormat('d M Y H:i'),
            'items' => $this->order->items->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->quantity * $item->price,
                    'formatted_price' => 'Rp ' . number_format($item->price, 0, ',', '.'),
                    'formatted_total' => 'Rp ' . number_format($item->quantity * $item->price, 0, ',', '.'),
                    'auto_delivery' => (bool) $item->productVariant?->isAutoDelivery(),
                ];
            })->toArray(),
            'subtotal' => $this->order->subtotal,
            'fee_amount' => $this->order->fee_amount,
            'total_amount' => $this->order->total_amount,
            'formatted_subtotal' => $this->order->formatted_subtotal,
            'formatted_fee_amount' => $this->order->formatted_fee_amount,
            'formatted_total_amount' => $this->order->formatted_total_amount,
            ...$this->order->emailAmounts(),
            'payment_method' => $this->order->paymentMethod->name ?? 'N/A',
            'has_digital_items' => $this->order->hasAutoDeliveryItems(),
            'needs_manual_delivery' => $this->order->needs_manual_delivery || $this->order->requiresManualDelivery(),
        ];
    }
}
