<?php

namespace App\Observers;

use App\Models\DigitalDelivery;
use App\Models\OrderItem;
use App\Models\DigitalStock;
use App\Models\GuestUser;
use App\Notifications\ManualDeliveryNotification;
use App\Notifications\ManualDeliveryCompletedNotification;
use Exception;

class DigitalDeliveryObserver
{
    /**
     * Handle the DigitalDelivery "created" event.
     * 
     * Automatically process manual delivery when a new delivery record is created
     * with delivery_trigger = 'manual'
     */
    public function created(DigitalDelivery $delivery): void
    {
        // Only process manual deliveries
        if ($delivery->delivery_trigger !== 'manual') {
            return;
        }

        // Log::info('Processing manual delivery creation', [
        //     'delivery_id' => $delivery->id,
        //     'order_item_id' => $delivery->order_item_id,
        // ]);

        try {
            // Process in a database transaction to ensure consistency
            \DB::transaction(function () use ($delivery) {
                $this->processManualDelivery($delivery);
            });

            // Log::info('Manual delivery processed successfully', [
            //     'delivery_id' => $delivery->id,
            // ]);

        } catch (Exception $e) {
            $delivery->update([
                'delivery_status' => DigitalDelivery::STATUS_FAILED,
            ]);
        }
    }

    /**
     * Process manual delivery workflow
     */
    protected function processManualDelivery(DigitalDelivery $delivery): void
    {
        // Log::info('Starting manual delivery processing', [
        //     'delivery_id' => $delivery->id,
        //     'current_status' => $delivery->delivery_status,
        //     'has_content' => !empty($delivery->delivery_content),
        //     'trigger' => $delivery->delivery_trigger,
        // ]);

        // Guard: Skip if already processed AND email already sent
        if ($delivery->delivery_status === DigitalDelivery::STATUS_COMPLETED && $delivery->email_sent) {
            // Log::info('Manual delivery already fully processed, skipping', [
            //     'delivery_id' => $delivery->id,
            // ]);
            return;
        }

        if (empty($delivery->delivery_content)) {
            $this->updateOrderItemStatus($delivery);
            $this->updateDigitalStockStatus($delivery);
            return;
        }

        // 1. Update OrderItem status to 'delivered'
        $this->updateOrderItemStatus($delivery);

        // 2. Update DigitalStock to 'delivered' with proper fields
        $this->updateDigitalStockStatus($delivery);

        // 3. Update delivery status to completed if not already
        // Use updateQuietly to prevent triggering observers and causing a loop
        $delivery->updateQuietly([
            'delivery_status' => DigitalDelivery::STATUS_COMPLETED,
            'delivered_at' => $delivery->delivered_at ?? now(),
            'email_sent' => false,
        ]);

        // 4. Send email notification with manual product body
        $this->sendManualDeliveryEmail($delivery);

        // Log::info('Manual delivery processing completed', [
        //     'delivery_id' => $delivery->id,
        // ]);
    }

    /**
     * Update OrderItem status to delivered
     */
    protected function updateOrderItemStatus(DigitalDelivery $delivery): void
    {
        $orderItem = $delivery->orderItem;

        if (!$orderItem) {
            return;
        }

        $orderItem->update([
            'delivery_status' => 'delivered',
        ]);

        // Log::info('OrderItem status updated to delivered', [
        //     'order_item_id' => $orderItem->id,
        // ]);
    }

    /**
     * Update DigitalStock to delivered status with proper fields
     */
    protected function updateDigitalStockStatus(DigitalDelivery $delivery): void
    {
        $stock = $delivery->digitalStock;

        if (!$stock) {
            return;
        }

        // Update stock with all necessary fields
        $stock->update([
            'status' => DigitalStock::STATUS_DELIVERED,
            'assigned_at' => $stock->assigned_at ?? now(), // Set if not already set
            'assigned_to_order_item_id' => $delivery->order_item_id,
            'delivered_at' => now(),
        ]);

        // Log::info('DigitalStock status updated to delivered', [
        //     'digital_stock_id' => $stock->id,
        // ]);
    }

    /**
     * Send manual delivery email notification with actual delivery content
     */
    protected function sendManualDeliveryEmail(DigitalDelivery $delivery): void
    {
        try {
            $orderItem = $delivery->orderItem()->with('order')->first();
            
            if (!$orderItem || !$orderItem->order) {
                return;
            }

            $order = $orderItem->order;

            // Determine the notifiable (User or GuestUser)
            $notifiable = $order->user_id 
                ? $order->user 
                : new GuestUser($order->customer_email, $order->customer_name, $order->customer_phone);

            if (!$notifiable) {
                return;
            }

            // Send the completed delivery notification with actual delivery content
            $notifiable->notify(new ManualDeliveryCompletedNotification($order));

            // Update delivery record using updateQuietly to prevent observer loops
            $delivery->updateQuietly([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);

            // Log::info('Manual delivery email with content sent successfully', [
            //     'delivery_id' => $delivery->id,
            //     'order_number' => $order->order_number,
            //     'customer_email' => $order->customer_email,
            //     'has_delivery_content' => !empty($delivery->delivery_content),
            // ]);

        } catch (Exception $e) {
        }
    }

    /**
     * Handle the DigitalDelivery "updated" event.
     * 
     * If delivery_trigger is changed to 'manual' and status is completed,
     * ensure all related records are properly updated
     */
    public function updated(DigitalDelivery $delivery): void
    {
        // Prevent infinite loop: Skip if this was just created or already completed
        if ($delivery->wasRecentlyCreated || $delivery->delivery_status === DigitalDelivery::STATUS_COMPLETED) {
            return;
        }

        // Only process if delivery_trigger ACTUALLY CHANGED from non-manual to manual
        // getOriginal() gets the value before the update
        $originalTrigger = $delivery->getOriginal('delivery_trigger');
        $newTrigger = $delivery->delivery_trigger;

        if ($originalTrigger !== 'manual' && $newTrigger === 'manual') {
            // Log::info('Delivery trigger changed to manual', [
            //     'delivery_id' => $delivery->id,
            //     'original_trigger' => $originalTrigger,
            //     'new_trigger' => $newTrigger,
            // ]);

            try {
                \DB::transaction(function () use ($delivery) {
                    $this->processManualDelivery($delivery);
                });
            } catch (Exception $e) {
                // Silent error handling for production
            }
        }
    }

    /**
     * Handle the DigitalDelivery "deleting" event.
     */
    public function deleting(DigitalDelivery $delivery): void
    {
        // Optional: Add logic to handle cleanup when a delivery is deleted
        // For example, you might want to revert the stock status back to available
        // if the delivery hasn't been downloaded yet
    }
}
