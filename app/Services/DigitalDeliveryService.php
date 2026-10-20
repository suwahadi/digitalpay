<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DigitalStock;
use App\Models\DigitalDelivery;
use App\Models\ProductVariant;
use App\Models\GuestUser;
use App\Notifications\DigitalDeliveryNotification;
use App\Notifications\ManualDeliveryNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

/**
 * Service untuk mengelola proses digital delivery
 * 
 * Handle semua logic untuk assign stock dan create delivery
 */
class DigitalDeliveryService
{
    /**
     * Process delivery untuk order - MODIFIKASI untuk support manual
     */
    public function processOrderDigitalDelivery(Order $order)
    {
        $results = [];

        foreach ($order->orderItems as $orderItem) {
            $variant = $orderItem->productVariant;
            
            if (!$variant || !$variant->product->isDigital()) {
                continue;
            }

            $existingDelivery = DigitalDelivery::where('order_item_id', $orderItem->id)
                ->where('delivery_status', '!=', DigitalDelivery::STATUS_FAILED)
                ->first();
                
            if ($existingDelivery) {
                $results[] = [
                    'order_item_id' => $orderItem->id,
                    'status' => 'already_delivered',
                    'delivery_id' => $existingDelivery->id
                ];
                continue;
            }

            try {
                $delivery = $this->processOrderItemDelivery($orderItem);
                $results[] = [
                    'order_item_id' => $orderItem->id,
                    'status' => 'success',
                    'delivery_id' => $delivery->id
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

    public function processManualDelivery(Order $order, $userId = null, $notes = null)
    {
        $order->markAsManuallyProcessed($userId, $notes);

        $results = [];
        foreach ($order->orderItems as $orderItem) {
            $variant = $orderItem->productVariant;
            
            if (!$variant || !$variant->product->isDigital() || $variant->auto_delivery) {
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
    
    /**
     * Process delivery untuk satu order item
     */
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
    
    /**
     * Assign available stock ke order item
     */
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
    
    /**
     * Generate konten file TXT untuk customer
     */
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
    
    /**
     * Create delivery record
     */
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
    
    /**
     * Get default template jika tidak ada custom template
     */
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
    
    /**
     * Get delivery records untuk order
     */
    public function getOrderDeliveries(Order $order)
    {
        return DigitalDelivery::whereHas('orderItem', function($query) use ($order) {
            $query->where('order_id', $order->id);
        })->with(['orderItem.productVariant.product', 'digitalStock'])->get();
    }
    
    /**
     * Check apakah order punya digital products
     */
    public function orderHasDigitalProducts(Order $order): bool
    {
        return $order->orderItems()
            ->whereHas('productVariant.product', function($query) {
                $query->where('is_digital', true);
            })->exists();
    }

    /**
     * Send digital delivery notification
     */
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

    /**
     * Send manual delivery notification
     */
    public function sendManualDeliveryNotification(Order $order): void
    {
        try {
            $notifiable = $order->user_id 
                ? $order->user 
                : new GuestUser($order->customer_email, $order->customer_name, $order->customer_phone);
            
            if ($notifiable) {
                $notifiable->notify(new ManualDeliveryNotification($order));
                
            }
        } catch (Exception $e) {
            report($e);
        }
    }
}
