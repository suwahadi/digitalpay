<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Services\BrevoService;

class ForgotPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $resetToken;
    protected string $userName;
    protected string $resetUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $resetToken, string $userName, string $resetUrl)
    {
        $this->resetToken = $resetToken;
        $this->userName = $userName;
        $this->resetUrl = $resetUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['brevo'];
    }

    /**
     * Send notification via Brevo
     */
    public function toBrevo($notifiable)
    {
        $brevoService = app(BrevoService::class);
        
        $emailData = [
            'to' => $notifiable->email,
            'recipient_name' => $this->userName,
            'subject' => 'Reset Password - ' . config('app.name'),
            'html_content' => $this->getEmailContent(),
            'tags' => ['password-reset', 'security']
        ];

        return $brevoService->sendEmail($emailData);
    }

    /**
     * Get the HTML content for the email
     */
    protected function getEmailContent(): string
    {
        $appName = config('app.name');
        $appUrl = config('app.url');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Reset Password - {$appName}</title>
            <style>
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                    line-height: 1.7; 
                    color: #374151; 
                    margin: 0;
                    padding: 0;
                    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                }
                .email-wrapper {
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px;
                }
                .container { 
                    background: #ffffff; 
                    padding: 32px; 
                    border-radius: 16px; 
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
                    border: 1px solid #e5e7eb;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 32px; 
                }
                .logo { 
                    font-size: 24px; 
                    font-weight: 700; 
                    color: #3b82f6; 
                    margin-bottom: 8px;
                    letter-spacing: -0.5px;
                }
                .title { 
                    font-size: 28px; 
                    font-weight: 600; 
                    color: #111827; 
                    margin: 0 0 12px 0;
                    letter-spacing: -0.5px;
                }
                .subtitle { 
                    color: #6b7280; 
                    font-size: 16px;
                    margin: 0;
                }
                .greeting {
                    font-size: 16px;
                    color: #4b5563;
                    margin: 24px 0;
                    text-align: center;
                }
                .button-section { 
                    text-align: center; 
                    margin: 40px 0; 
                }
                .reset-button {
                    display: inline-block;
                    padding: 16px 32px;
                    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 12px;
                    font-weight: 600;
                    font-size: 16px;
                    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
                    transition: all 0.3s ease;
                }
                .reset-button:hover {
                    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
                    transform: translateY(-2px);
                }
                .validity-note {
                    background: #fef3c7; 
                    border: 1px solid #f59e0b; 
                    border-radius: 8px; 
                    padding: 16px; 
                    margin: 24px 0;
                    text-align: center;
                }
                .validity-note strong {
                    color: #92400e;
                    font-weight: 600;
                }
                .validity-note p {
                    margin: 4px 0 0 0;
                    color: #b45309;
                    font-size: 14px;
                }
                .security-note {
                    background: #f3f4f6; 
                    border-radius: 8px; 
                    padding: 20px; 
                    margin: 24px 0;
                    border-left: 4px solid #6b7280;
                }
                .security-note strong {
                    color: #374151;
                    font-weight: 600;
                    display: block;
                    margin-bottom: 8px;
                }
                .security-note ul {
                    margin: 0;
                    padding-left: 16px;
                    color: #6b7280;
                    font-size: 14px;
                }
                .security-note li {
                    margin: 4px 0;
                }
                .alternative-link {
                    margin-top: 24px;
                    padding: 16px;
                    background: #f9fafb;
                    border-radius: 8px;
                    border: 1px solid #e5e7eb;
                    word-break: break-all;
                }
                .alternative-link p {
                    margin: 0 0 8px 0;
                    font-size: 12px;
                    color: #6b7280;
                }
                .alternative-link a {
                    color: #3b82f6;
                    text-decoration: none;
                    font-size: 13px;
                }
                .footer { 
                    text-align: center; 
                    margin-top: 32px; 
                    padding-top: 24px; 
                    border-top: 1px solid #e5e7eb; 
                    color: #9ca3af; 
                    font-size: 13px;
                }
                .footer a { 
                    color: #3b82f6; 
                    text-decoration: none;
                    font-weight: 500;
                }
                .footer a:hover {
                    text-decoration: underline;
                }
                
                /* Mobile Responsive */
                @media only screen and (max-width: 600px) {
                    .email-wrapper {
                        padding: 16px;
                    }
                    .container {
                        padding: 24px 20px;
                        border-radius: 12px;
                    }
                    .title {
                        font-size: 24px;
                    }
                    .reset-button {
                        padding: 14px 28px;
                        font-size: 15px;
                    }
                }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='container'>
                    <div class='header'>
                        <div class='logo'>{$appName}</div>
                        <h2 class='title'>Reset Password</h2>
                    </div>

                    <p class='greeting'>Halo <strong>{$this->userName}</strong>,<br>
                    Kami menerima permintaan untuk mereset password akun Anda. Klik tombol di bawah ini untuk membuat password baru.</p>

                    <div class='button-section'>
                        <a href='{$this->resetUrl}' class='reset-button'>Reset Password</a>
                    </div>

                    <div class='alternative-link'>
                        <p><strong>Atau salin link berikut ke browser Anda:</strong></p>
                        <a href='{$this->resetUrl}'>{$this->resetUrl}</a>
                    </div>

                    <div class='validity-note'>
                        <strong>⏰ Link berlaku selama 60 menit</strong>
                        <p>Segera gunakan link ini untuk mereset password Anda</p>
                    </div>

                    <div class='security-note'>
                        <strong>🔒 Catatan Keamanan</strong>
                        <ul>
                            <li>Jangan bagikan link ini kepada siapa pun</li>
                            <li>Tim {$appName} tidak akan pernah meminta password atau link reset Anda</li>
                            <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
                            <li>Password Anda tidak akan berubah sampai Anda membuat password baru</li>
                        </ul>
                    </div>

                    <div class='footer'>
                        <p>Email otomatis dari {$appName} • <a href='{$appUrl}'>Kunjungi Website</a></p>
                        <p>Butuh bantuan? Hubungi tim support kami</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
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
