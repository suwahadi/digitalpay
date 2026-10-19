<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Services\BrevoService;

class EmailVerificationOTP extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $otpCode;
    protected string $userName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otpCode, string $userName)
    {
        $this->otpCode = $otpCode;
        $this->userName = $userName;
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
            'subject' => 'Kode Verifikasi Email - ' . config('app.name'),
            'html_content' => $this->getEmailContent(),
            'tags' => ['verification', 'otp', 'registration']
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
            <title>Verifikasi Email - {$appName}</title>
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
                .otp-section { 
                    text-align: center; 
                    margin: 40px 0; 
                    padding: 32px 20px; 
                    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); 
                    border-radius: 12px;
                    border: 1px solid #bae6fd;
                }
                .otp-label { 
                    font-size: 14px; 
                    color: #0369a1; 
                    margin-bottom: 16px; 
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .otp-code { 
                    font-size: 42px; 
                    font-weight: 800; 
                    color: #1d4ed8; 
                    letter-spacing: 6px; 
                    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace;
                    margin: 0;
                    text-shadow: 0 2px 4px rgba(29, 78, 216, 0.1);
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
                    .otp-code {
                        font-size: 36px;
                        letter-spacing: 4px;
                    }
                    .otp-section {
                        padding: 24px 16px;
                        margin: 32px 0;
                    }
                }
                
                @media only screen and (max-width: 480px) {
                    .otp-code {
                        font-size: 32px;
                        letter-spacing: 3px;
                    }
                }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='container'>
                    <div class='header'>
                        <div class='logo'>{$appName}</div>
                        <h2 class='title'>Verifikasi Email</h2>
                    </div>

                    <p class='greeting'>Halo <strong>{$this->userName}</strong>,<br>
                    Terima kasih telah bergabung dengan {$appName}. Masukkan kode verifikasi di bawah ini untuk mengaktifkan akun Anda.</p>

                    <div class='otp-section'>
                        <div class='otp-label'>Kode Verifikasi:</div>
                        <div class='otp-code'>{$this->otpCode}</div>
                    </div>

                    <div class='validity-note'>
                        <strong>⏰ Kode berlaku selama 10 menit</strong>
                        <p>Segera masukkan kode ini di halaman verifikasi</p>
                    </div>

                    <div class='security-note'>
                        <strong>🔒 Catatan Keamanan</strong>
                        <ul>
                            <li>Jangan bagikan kode ini kepada siapa pun</li>
                            <li>Tim {$appName} tidak akan pernah meminta kode OTP Anda</li>
                            <li>Jika Anda tidak melakukan registrasi, abaikan email ini</li>
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
            'otp_code' => $this->otpCode,
            'user_name' => $this->userName,
            'sent_at' => now(),
        ];
    }
}