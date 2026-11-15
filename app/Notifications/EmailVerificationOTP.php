<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Services\BrevoService;

class EmailVerificationOTP extends Notification
{
    protected string $otpCode;
    protected string $userName;

    public function __construct(string $otpCode, string $userName)
    {
        $this->otpCode = $otpCode;
        $this->userName = $userName;
    }

    public function via(object $notifiable): array
    {
        return ['brevo'];
    }

    public function toBrevo($notifiable)
    {
        $brevoService = app(BrevoService::class);

        $emailData = [
            'to' => $notifiable->email,
            'recipient_name' => $this->userName,
            'subject' => BrevoService::subject('email_verification', config('app.name')),
            'html_content' => $this->getEmailContent(),
            'tags' => ['verification', 'otp', 'registration']
        ];

        return $brevoService->sendEmail($emailData);
    }

    protected function getEmailContent(): string
    {
        return view('emails.otp-verification', ['otp_code' => $this->otpCode, 'user_name' => $this->userName])->render();
    }

    public function toArray(object $notifiable): array
    {
        return [
            'otp_code' => $this->otpCode,
            'user_name' => $this->userName,
            'sent_at' => now(),
        ];
    }
}