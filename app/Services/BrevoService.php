<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class BrevoService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected bool $enabled;
    protected string $senderEmail;
    protected string $senderName;

    public function __construct()
    {
        $this->apiKey = config('services.brevo.api_key');
        $this->apiUrl = config('services.brevo.api_url');
        $this->enabled = config('services.brevo.enabled', false);
        $this->senderEmail = config('services.brevo.sender_email');
        $this->senderName = config('services.brevo.sender_name');
    }

    /**
     * Send transactional email via Brevo API
     *
     * @param array $data
     * @return array
     */
    public function sendEmail(array $data): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Brevo email service is disabled'
            ];
        }

        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Brevo API key is not configured'
            ];
        }

        try {
            $payload = $this->preparePayload($data);
            
            $response = Http::withHeaders([
                'to' => $data['to'] ?? null,
                'subject' => $data['subject'] ?? null
            ]);

            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/smtp/email", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Email sent successfully',
                    'data' => $response->json()
                ];
            }

            Log::warning('Brevo API rejected email', [
                'to' => $data['to'] ?? null,
                'subject' => $data['subject'] ?? null,
                'status' => $response->status(),
                'error' => $response->json() ?? $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send email',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::warning('Brevo API request failed', [
                'to' => $data['to'] ?? null,
                'subject' => $data['subject'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Prepare email payload for Brevo API
     *
     * @param array $data
     * @return array
     */
    protected function preparePayload(array $data): array
    {
        $payload = [
            'sender' => [
                'name' => $data['sender_name'] ?? $this->senderName,
                'email' => $data['sender_email'] ?? $this->senderEmail,
            ],
            'subject' => $data['subject'],
        ];

        if (isset($data['to'])) {
            if (is_string($data['to'])) {
                $payload['to'] = [
                    ['email' => $data['to']]
                ];
            } elseif (is_array($data['to'])) {
                $payload['to'] = array_map(function ($recipient) {
                    if (is_string($recipient)) {
                        return ['email' => $recipient];
                    }
                    return $recipient;
                }, $data['to']);
            }
        }

        if (isset($data['recipient_name']) && isset($payload['to'][0])) {
            $payload['to'][0]['name'] = $data['recipient_name'];
        }

        if (isset($data['html_content'])) {
            $payload['htmlContent'] = $data['html_content'];
        }

        if (isset($data['text_content'])) {
            $payload['textContent'] = $data['text_content'];
        }

        if (isset($data['template_id'])) {
            $payload['templateId'] = $data['template_id'];
            
            if (isset($data['params'])) {
                $payload['params'] = $data['params'];
            }
        }

        if (isset($data['cc'])) {
            $payload['cc'] = is_array($data['cc']) 
                ? $data['cc'] 
                : [['email' => $data['cc']]];
        }

        if (isset($data['bcc'])) {
            $payload['bcc'] = is_array($data['bcc']) 
                ? $data['bcc'] 
                : [['email' => $data['bcc']]];
        }

        if (isset($data['reply_to'])) {
            $payload['replyTo'] = is_array($data['reply_to']) 
                ? $data['reply_to'] 
                : ['email' => $data['reply_to']];
        }

        if (isset($data['attachments'])) {
            $payload['attachment'] = $data['attachments'];
        }

        if (isset($data['tags'])) {
            $payload['tags'] = is_array($data['tags']) ? $data['tags'] : [$data['tags']];
        }

        return $payload;
    }

    /**
     * Send order confirmation email
     *
     * @param array $orderData
     * @return array
     */
    public function sendOrderConfirmation(array $orderData): array
    {
        $htmlContent = view('emails.order-confirmation', $orderData)->render();
        
        return $this->sendEmail([
            'to' => $orderData['customer_email'],
            'recipient_name' => $orderData['customer_name'],
            'subject' => 'Konfirmasi Pesanan - ' . $orderData['order_number'],
            'html_content' => $htmlContent,
            'tags' => ['order', 'confirmation']
        ]);
    }

    /**
     * Send payment success email
     *
     * @param array $orderData
     * @return array
     */
    public function sendPaymentSuccess(array $orderData): array
    {
        $htmlContent = view('emails.payment-success', $orderData)->render();
        
        return $this->sendEmail([
            'to' => $orderData['customer_email'],
            'recipient_name' => $orderData['customer_name'],
            'subject' => 'Pembayaran Berhasil - ' . $orderData['order_number'],
            'html_content' => $htmlContent,
            'tags' => ['order', 'payment', 'success']
        ]);
    }

    /**
     * Send digital delivery email
     *
     * @param array $deliveryData
     * @return array
     */
    public function sendDigitalDelivery(array $deliveryData): array
    {
        $htmlContent = view('emails.digital-delivery', $deliveryData)->render();
        
        return $this->sendEmail([
            'to' => $deliveryData['customer_email'],
            'recipient_name' => $deliveryData['customer_name'],
            'subject' => 'Pengiriman Digital - ' . $deliveryData['order_number'],
            'html_content' => $htmlContent,
            'tags' => ['order', 'delivery', 'digital']
        ]);
    }

    /**
     * Send manual delivery notification email
     *
     * @param array $orderData
     * @return array
     */
    public function sendManualDeliveryNotification(array $orderData): array
    {
        $htmlContent = view('emails.manual-delivery-notification', $orderData)->render();
        
        return $this->sendEmail([
            'to' => $orderData['customer_email'],
            'recipient_name' => $orderData['customer_name'],
            'subject' => 'Pesanan Anda Sedang Diproses - ' . $orderData['order_number'],
            'html_content' => $htmlContent,
            'tags' => ['order', 'manual-delivery', 'notification']
        ]);
    }

    /**
     * Send manual delivery completed email (with actual delivery content)
     *
     * @param array $orderData
     * @return array
     */
    public function sendManualDeliveryCompleted(array $orderData): array
    {
        $htmlContent = view('emails.manual-delivery-completed', $orderData)->render();
        
        return $this->sendEmail([
            'to' => $orderData['customer_email'],
            'recipient_name' => $orderData['customer_name'],
            'subject' => 'Produk Digital Anda Telah Dikirim - ' . $orderData['order_number'],
            'html_content' => $htmlContent,
            'tags' => ['order', 'manual-delivery', 'completed', 'digital']
        ]);
    }

    /**
     * Send order cancellation email
     *
     * @param array $orderData
     * @return array
     */
    public function sendOrderCancellation(array $orderData): array
    {
        $htmlContent = view('emails.order-cancellation', $orderData)->render();
        
        return $this->sendEmail([
            'to' => $orderData['customer_email'],
            'recipient_name' => $orderData['customer_name'],
            'subject' => 'Pesanan Dibatalkan - ' . $orderData['order_number'],
            'html_content' => $htmlContent,
            'tags' => ['order', 'cancellation']
        ]);
    }

    /**
     * Get account information from Brevo
     *
     * @return array
     */
    public function getAccount(): array
    {
        if (!$this->enabled || empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Brevo is not configured properly'
            ];
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/account");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test email connection
     *
     * @param string $testEmail
     * @return array
     */
    public function testConnection(string $testEmail): array
    {
        return $this->sendEmail([
            'to' => $testEmail,
            'subject' => 'Test Email from Brevo Integration',
            'html_content' => '<h1>Test Email</h1><p>This is a test email from your Brevo integration.</p>',
            'tags' => ['test']
        ]);
    }
}
