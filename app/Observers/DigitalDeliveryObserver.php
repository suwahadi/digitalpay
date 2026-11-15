<?php

namespace App\Observers;

use App\Models\DigitalDelivery;
use App\Models\OrderItem;
use App\Models\DigitalStock;
use App\Models\GuestUser;
use App\Notifications\ManualDeliveryCompletedNotification;
use Exception;

class DigitalDeliveryObserver
{
    public function created(DigitalDelivery $delivery): void
    {
        if ($delivery->delivery_trigger !== 'manual') {
            return;
        }

        try {
            \DB::transaction(function () use ($delivery) {
                $this->processManualDelivery($delivery);
            });
        } catch (Exception $e) {
            $delivery->update([
                'delivery_status' => DigitalDelivery::STATUS_FAILED,
            ]);
        }
    }

    protected function processManualDelivery(DigitalDelivery $delivery): void
    {
        if ($delivery->delivery_status === DigitalDelivery::STATUS_COMPLETED && $delivery->email_sent) {
            return;
        }

        if (empty($delivery->delivery_content)) {
            $this->updateOrderItemStatus($delivery);
            $this->updateDigitalStockStatus($delivery);
            return;
        }

        $this->updateOrderItemStatus($delivery);

        $this->updateDigitalStockStatus($delivery);

        $delivery->updateQuietly([
            'delivery_status' => DigitalDelivery::STATUS_COMPLETED,
            'delivered_at' => $delivery->delivered_at ?? now(),
            'email_sent' => false,
        ]);

        $this->sendManualDeliveryEmail($delivery);
    }

    protected function updateOrderItemStatus(DigitalDelivery $delivery): void
    {
        $orderItem = $delivery->orderItem;

        if (!$orderItem) {
            return;
        }

        $orderItem->update([
            'delivery_status' => 'delivered',
        ]);
    }

    protected function updateDigitalStockStatus(DigitalDelivery $delivery): void
    {
        $stock = $delivery->digitalStock;

        if (!$stock) {
            return;
        }

        $stock->update([
            'status' => DigitalStock::STATUS_DELIVERED,
            'assigned_at' => $stock->assigned_at ?? now(),
            'assigned_to_order_item_id' => $delivery->order_item_id,
            'delivered_at' => now(),
        ]);
    }

    protected function sendManualDeliveryEmail(DigitalDelivery $delivery): void
    {
        try {
            $orderItem = $delivery->orderItem()->with('order')->first();

            if (!$orderItem || !$orderItem->order) {
                return;
            }

            $order = $orderItem->order;

            $notifiable = $order->user_id
                ? $order->user
                : new GuestUser($order->customer_email, $order->customer_name, $order->customer_phone);

            if (!$notifiable) {
                return;
            }

            $notifiable->notify(new ManualDeliveryCompletedNotification($order));

            $delivery->updateQuietly([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);
        } catch (Exception $e) {
        }
    }

    public function updated(DigitalDelivery $delivery): void
    {
        if ($delivery->wasRecentlyCreated || $delivery->delivery_status === DigitalDelivery::STATUS_COMPLETED) {
            return;
        }

        $originalTrigger = $delivery->getOriginal('delivery_trigger');
        $newTrigger = $delivery->delivery_trigger;

        if ($originalTrigger !== 'manual' && $newTrigger === 'manual') {
            try {
                \DB::transaction(function () use ($delivery) {
                    $this->processManualDelivery($delivery);
                });
            } catch (Exception $e) {
            }
        }
    }

    public function deleting(DigitalDelivery $delivery): void
    {
    }
}
