<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DigitalStock;
use App\Models\DigitalDelivery;
use App\Models\ProductVariant;
use App\Models\GuestUser;
use App\Notifications\DigitalDeliveryNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class DigitalDeliveryService
{
    public function processOrderDigitalDelivery(Order $order)
    {
        $results = [];
        $needsManual = false;

        foreach ($order->orderItems as $orderItem) {
            $variant = $orderItem->productVariant;

            if (! $variant) {
                continue;
            }

            $existingDelivery = DigitalDelivery::where('order_item_id', $orderItem->id)
                ->where('delivery_status', '!=', DigitalDelivery::STATUS_FAILED)
                ->first();

            if ($existingDelivery) {
                $results[] = [
                    'order_item_id' => $orderItem->id,
                    'status' => 'already_delivered',
                    'delivery_id' => $existingDelivery->id,
                ];

                continue;
            }

            if ($variant->isManualDelivery()) {
                $variant->decrementStock((int) $orderItem->quantity);
                $needsManual = true;
                $results[] = ['order_item_id' => $orderItem->id, 'status' => 'manual'];

                continue;
            }

            try {
                $delivery = $this->processOrderItemDelivery($orderItem);
                $results[] = [
                    'order_item_id' => $orderItem->id,
                    'status' => 'success',
                    'delivery_id' => $delivery->id,
                ];
            } catch (Exception $e) {
                report($e);
                $needsManual = true;
                $results[] = [
                    'order_item_id' => $orderItem->id,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        if ($needsManual && ! $order->needs_manual_delivery) {
            $order->markAsManualDeliveryQueued();
        }

        $successfulDeliveries = array_filter($results, fn ($r) => $r['status'] === 'success');
        if (count($successfulDeliveries) > 0) {
            $this->sendDigitalDeliveryNotification($order);
        }

        return $results;
    }

    public function pendingManualItems(Order $order)
    {
        return $order->orderItems()
            ->with('productVariant')
            ->whereDoesntHave('digitalDelivery', fn ($q) => $q->where('delivery_status', '!=', DigitalDelivery::STATUS_FAILED))
            ->get();
    }

    public function deliverManually(OrderItem $orderItem, string $content, ?int $userId = null, ?string $notes = null): DigitalDelivery
    {
        $variant = $orderItem->productVariant;

        $delivery = DigitalDelivery::create([
            'order_item_id' => $orderItem->id,
            'digital_stock_id' => null,
            'delivery_content' => $content,
            'delivery_status' => DigitalDelivery::STATUS_PROCESSING,
            'delivery_trigger' => 'manual',
            'manual_notes' => $notes,
            'manually_triggered_at' => now(),
            'triggered_by_user_id' => $userId,
            'max_downloads' => $variant?->max_downloads ?? 5,
            'expires_at' => now()->addHours($variant?->download_expiry_hours ?? 48),
            'delivered_at' => now(),
            'delivery_metadata' => ['manual' => true, 'generated_at' => now()->toISOString()],
        ]);

        $order = $orderItem->order()->first();

        if ($order && $this->pendingManualItems($order)->isEmpty()) {
            $order->markAsManuallyProcessed($userId, $notes);
        }

        return $delivery->fresh();
    }

    public function processManualDelivery(Order $order, $userId = null, $notes = null)
    {
        $order->markAsManuallyProcessed($userId, $notes);

        $results = [];
        foreach ($order->orderItems as $orderItem) {
            $variant = $orderItem->productVariant;

            if (! $variant || $variant->isAutoDelivery()) {
                continue;
            }

            try {
                $delivery = $this->processOrderItemDelivery($orderItem);

                $delivery->markAsManuallyTriggered($userId, $notes);

                $results[] = [
                    'order_item_id' => $orderItem->id,
                    'status' => 'success',
                    'delivery_id' => $delivery->id,
                    'trigger' => 'manual'
                ];
            } catch (Exception $e) {
                report($e);
                $results[] = [
                    'order_item_id' => $orderItem->id,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        $successfulDeliveries = array_filter($results, fn($r) => $r['status'] === 'success');
        if (count($successfulDeliveries) > 0) {
            $this->sendDigitalDeliveryNotification($order);
        }

        return $results;
    }

    public function processOrderItemDelivery(OrderItem $orderItem): DigitalDelivery
    {
        $variant = $orderItem->productVariant;

        $stock = $this->assignAvailableStock($variant, $orderItem);

        if (!$stock) {
            throw new Exception("No available digital stock for variant: " . $variant->name);
        }

        $content = $this->generateDigitalContent($stock, $orderItem);

        $delivery = $this->createDeliveryRecord($orderItem, $stock, $content);

        $stock->markAsDelivered();

        return $delivery;
    }

    protected function assignAvailableStock(ProductVariant $variant, OrderItem $orderItem): ?DigitalStock
    {
        return DB::transaction(function () use ($variant, $orderItem) {
            $stock = $variant->getNextAvailableStock(lock: true);

            if (!$stock || !$stock->isValid()) {
                return null;
            }

            return $stock->assignToOrderItem($orderItem) ? $stock : null;
        });
    }

    protected function generateDigitalContent(DigitalStock $stock, OrderItem $orderItem): string
    {
        $order = $orderItem->order;
        $variant = $orderItem->productVariant;
        $product = $variant->product;

        $template = $variant->getDeliveryTemplate() ?? $this->getDefaultTemplate();

        $replacements = [
            '{order_number}' => $order->order_number,
            '{order_date}' => $order->created_at->translatedFormat('d M Y H:i'),
            '{customer_name}' => $order->customer_name,
            '{customer_email}' => $order->customer_email,
            '{product_name}' => $product->name,
            '{variant_name}' => $variant->name,
            '{price}' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
            '{duration}' => $variant->duration_days . ' hari',
        ];

        foreach ($stock->stock_data as $key => $value) {
            $replacements['{' . $key . '}'] = $value;
        }

        $content = str_replace(array_keys($replacements), array_values($replacements), $template);

        return $content;
    }

    protected function createDeliveryRecord(OrderItem $orderItem, DigitalStock $stock, string $content): DigitalDelivery
    {
        $variant = $orderItem->productVariant;

        $delivery = DigitalDelivery::create([
            'order_item_id' => $orderItem->id,
            'digital_stock_id' => $stock->id,
            'delivery_content' => $content,
            'delivery_status' => DigitalDelivery::STATUS_COMPLETED,
            'max_downloads' => $variant->max_downloads,
            'expires_at' => now()->addHours($variant->download_expiry_hours),
            'delivered_at' => now(),
            'delivery_metadata' => [
                'stock_name' => $stock->stock_name,
                'auto_generated' => true,
                'generated_at' => now()->toISOString()
            ]
        ]);

        $orderItem->update([
            'delivery_status' => 'delivered'
        ]);

        return $delivery;
    }

    protected function getDefaultTemplate(): string
    {
        return '=== DIGITAL DELIVERY ===
Produk: {product_name}
Variant: {variant_name}
Order: {order_number}
Tanggal: {order_date}
Harga: {price}

=== INFORMASI AKSES ===
Username: {username}
Password: {password}
Durasi: {duration}

=== CARA PENGGUNAAN ===
1. Gunakan informasi di atas untuk mengakses layanan
2. Jangan bagikan informasi ini kepada orang lain
3. Akun berlaku sesuai durasi yang tertera

=== SUPPORT ===
Jika ada masalah, hubungi:
Email: ' . \App\Helpers\ConfigHelper::get('contact_email', 'admin@digitalpay.web.id') . '
WhatsApp: +' . \App\Helpers\ConfigHelper::get('contact_whatsapp', '6281234567890') . '

Terima kasih telah berbelanja!';
    }

    public function getOrderDeliveries(Order $order)
    {
        return DigitalDelivery::whereHas('orderItem', function($query) use ($order) {
            $query->where('order_id', $order->id);
        })->with(['orderItem.productVariant.product', 'digitalStock'])->get();
    }

    public function orderHasDigitalProducts(Order $order): bool
    {
        return $order->orderItems()->exists();
    }

    protected function sendDigitalDeliveryNotification(Order $order): void
    {
        try {
            $notifiable = $order->user_id
                ? $order->user
                : new GuestUser($order->customer_email, $order->customer_name, $order->customer_phone);

            if ($notifiable) {
                $notifiable->notify(new DigitalDeliveryNotification($order));
            }
        } catch (Exception $e) {
            report($e);
        }
    }
}
