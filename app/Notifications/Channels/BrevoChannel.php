<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BrevoChannel
{
    /**
     * Send the given notification.
     *
     * Skipped when Brevo is disabled. Failures are thrown so a queued
     * notification is retried and lands in failed_jobs, and a synchronous
     * caller can tell the user it failed.
     *
     * @param  mixed  $notifiable
     * @return void
     *
     * @throws \RuntimeException
     */
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toBrevo') || ! config('services.brevo.enabled')) {
            return;
        }

        $result = $notification->toBrevo($notifiable);
        $sent = is_array($result) ? (bool) ($result['success'] ?? false) : (bool) $result;

        if (! $sent) {
            $recipient = $notifiable->routeNotificationFor('brevo', $notification) ?? ($notifiable->email ?? null);

            Log::error('Brevo notification was not sent', [
                'notification' => $notification::class,
                'recipient' => $recipient,
            ]);

            throw new RuntimeException('Brevo failed to send '.class_basename($notification).' to '.$recipient);
        }
    }
}
