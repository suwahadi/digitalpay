<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\GuestUser;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\OrderCancellationNotification;
use App\Notifications\OrderExpiredNotification;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        try {
            $notifiable = $this->getNotifiable($order);
            
            if ($notifiable) {
                $notifiable->notify(new OrderCreatedNotification($order));
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            try {
                $notifiable = $this->getNotifiable($order);
                
                if ($notifiable) {
                    $notifiable->notify(new PaymentSuccessNotification($order));
                }
            } catch (\Exception $e) {
                report($e);
            }
        }

        if ($order->isDirty('status') && $order->status === 'cancelled') {
            try {
                $notifiable = $this->getNotifiable($order);
                
                if ($notifiable) {
                    $reason = $order->notes ?? 'Pesanan dibatalkan';
                    $notifiable->notify(new OrderCancellationNotification($order, $reason));
                }
            } catch (\Exception $e) {
                report($e);
            }
        }

        if ($order->isDirty('status') && $order->status === 'expired') {
            try {
                $notifiable = $this->getNotifiable($order);

                if ($notifiable) {
                    $notifiable->notify(new OrderExpiredNotification($order));
                }
            } catch (\Exception $e) {
                report($e);
            }
        }
    }

    /**
     * Get notifiable entity (User or GuestUser)
     */
    protected function getNotifiable(Order $order)
    {
        if ($order->user_id) {
            return $order->user;
        }

        if ($order->customer_email) {
            return new GuestUser(
                $order->customer_email,
                $order->customer_name,
                $order->customer_phone
            );
        }

        return null;
    }
}
