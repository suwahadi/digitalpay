<?php

namespace App\Observers;

use App\Helpers\ConfigHelper;
use App\Models\Order;
use App\Notifications\AdminPaymentReceivedNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\GuestUser;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\OrderCancellationNotification;
use App\Notifications\OrderExpiredNotification;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->isQrisgate()) {
            return;
        }

        $this->notifyOrderCreated($order);
    }

    public function notifyOrderCreated(Order $order): void
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

        if ($order->isQrisgate()) {
            return;
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

    public function notifyAdminPaymentReceived(Order $order): void
    {
        $adminEmail = ConfigHelper::contactSettings()['email'] ?? null;

        if (! $adminEmail || ! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Notification::route('brevo', $adminEmail)
                ->notify(new AdminPaymentReceivedNotification($order, $adminEmail));
        } catch (\Exception $e) {
            report($e);
        }
    }

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
