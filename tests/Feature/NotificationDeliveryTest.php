<?php

namespace Tests\Feature;

use App\Notifications\EmailVerificationOTP;
use App\Notifications\ForgotPasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

class NotificationDeliveryTest extends TestCase
{
    public function test_auth_notifications_send_synchronously(): void
    {
        $this->assertNotInstanceOf(ShouldQueue::class, new EmailVerificationOTP('123456', 'Budi'));
        $this->assertNotInstanceOf(ShouldQueue::class, new ForgotPasswordNotification('token', 'Budi', 'https://x/reset'));
    }
}
