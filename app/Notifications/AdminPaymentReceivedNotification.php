<?php

namespace App\Notifications;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminPaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Order $order, protected string $adminEmail)
    {
    }

    public function via(object $notifiable): array
    {
        return ['brevo'];
    }

    public function toBrevo(object $notifiable): bool
    {
        $result = app(BrevoService::class)->sendAdminPaymentReceived($this->orderData());

        return $result['success'] ?? false;
    }

    public function orderData(): array
    {
        $this->order->load('items', 'paymentMethod');

        return [
            'admin_email' => $this->adminEmail,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer_name,
            'customer_email' => $this->order->customer_email,
            'customer_phone' => $this->order->customer_phone,
            'payment_method' => $this->order->paymentMethod->name ?? 'N/A',
            'payment_date' => ($this->order->midtrans_paid_at ?? now())->translatedFormat('d M Y H:i'),
            'items' => $this->order->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'variant_name' => $item->variant_name,
                'quantity' => $item->quantity,
            ])->toArray(),
            'formatted_total_amount' => $this->order->formatted_total_amount,
            'needs_manual_delivery' => $this->needsManualDelivery(),
            'admin_url' => OrderResource::getUrl('view', ['record' => $this->order], panel: 'admin'),
        ];
    }

    private function needsManualDelivery(): bool
    {
        if ($this->order->needs_manual_delivery || $this->order->requiresManualDelivery()) {
            return true;
        }

        return $this->order->items()
            ->whereDoesntHave('digitalDelivery')
            ->whereHas('productVariant', fn ($q) => $q->where('delivery_type', ProductVariant::DELIVERY_AUTO)
                ->whereDoesntHave('digitalStocks', fn ($stock) => $stock->usable()))
            ->exists();
    }
}
