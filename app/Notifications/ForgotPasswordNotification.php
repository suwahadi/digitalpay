<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Services\BrevoService;

class ForgotPasswordNotification extends Notification
{
    protected string $resetToken;
    protected string $userName;
    protected string $resetUrl;

    public function __construct(string $resetToken, string $userName, string $resetUrl)
    {
        $this->resetToken = $resetToken;
        $this->userName = $userName;
        $this->resetUrl = $resetUrl;
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
            'subject' => BrevoService::subject('password_reset', config('app.name')),
            'html_content' => $this->getEmailContent(),
            'tags' => ['password-reset', 'security']
        ];

        return $brevoService->sendEmail($emailData);
    }

    protected function getEmailContent(): string
    {
        return view('emails.password-reset', ['reset_url' => $this->resetUrl, 'user_name' => $this->userName])->render();
    }

    public function toArray(object $notifiable): array
    {
        return [
            'reset_token' => $this->resetToken,
            'user_name' => $this->userName,
            'reset_url' => $this->resetUrl,
            'sent_at' => now(),
        ];
    }
}
