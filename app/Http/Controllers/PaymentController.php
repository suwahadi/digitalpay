<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Helpers\ConfigHelper;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Show payment instructions
     */
    public function show(Request $request, $orderNumber)
    {
        $order = Order::with(['items.product', 'items.productVariant', 'paymentMethod'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $payment = $order->payment;
        
        if (!$payment) {
            return redirect()->route('home')
                ->with('error', 'Payment data not found.');
        }

        $paymentInstructions = session('payment_instructions');
        $paymentData = session('payment_data');
        
        if (!$paymentInstructions && $payment->gateway_response) {
            $gatewayData = is_string($payment->gateway_response) ? 
                json_decode($payment->gateway_response, true) : 
                $payment->gateway_response;
                
            if ($gatewayData) {
                $paymentInstructions = $this->midtransService->extractPaymentInstructions($gatewayData);
            }
        }

        $qrString = null;
        if ($paymentInstructions && isset($paymentInstructions['qr_string'])) {
            $qrString = $paymentInstructions['qr_string'];
        }

        $siteName = ConfigHelper::siteName();

        return Inertia::render('Payment/Show', [
            'title' => 'Payment Instructions - ' . $siteName,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'uuid' => $order->uuid,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'subtotal' => (int) ceil($order->subtotal),
                'discount_amount' => (int) ceil($order->discount_amount ?? 0),
                'fee_amount' => (int) ceil($order->fee_amount),
                'total_amount' => (int) ceil($order->total_amount),
                'total' => (int) ceil($order->total_amount),
                'expires_at' => $order->expires_at,
                'created_at' => $order->created_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'variant_name' => $item->variant_name,
                        'quantity' => $item->quantity,
                        'price' => (int) ceil($item->price),
                        'total' => (int) ceil($item->total),
                        'product_image' => $item->product?->thumb_url ?? '/images/placeholder.png',
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'slug' => $item->product->slug,
                            'name' => $item->product->name,
                        ] : null,
                    ];
                })
            ],
            'payment' => [
                'id' => $payment->id,
                'uuid' => $payment->uuid,
                'status' => $payment->status,
                'total_amount' => (int) ceil($payment->total_amount),
                'expired_at' => $payment->expired_at,
                'gateway_transaction_id' => $payment->gateway_transaction_id,
            ],
            'paymentMethod' => [
                'id' => $order->paymentMethod->id,
                'name' => $order->paymentMethod->name,
                'type' => $order->paymentMethod->type,
                'provider' => $order->paymentMethod->provider,
                'display_name' => $order->paymentMethod->getCoreApiConfig()['display_name'],
                'logo' => $order->paymentMethod->getCoreApiConfig()['logo'],
                'description' => $order->paymentMethod->getDescription(),
                'is_instant' => $order->paymentMethod->isInstantPayment(),
                'estimated_time' => $order->paymentMethod->getEstimatedPaymentTime(),
            ],
            'paymentInstructions' => $paymentInstructions,
            'paymentData' => $paymentData,
            'qrString' => $qrString,
        ]);
    }

    /**
     * Check payment status (called by frontend for real-time updates)
     */
    public function checkStatus(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        // Check if there's a payment success callback in session (from Midtrans callback)
        $successCallback = session()->get('payment_success_callback');
        if ($successCallback && $successCallback['order_number'] === $orderNumber) {
            session()->forget('payment_success_callback');
            
            return response()->json([
                'status' => 'success',
                'payment_status' => 'paid',
                'transaction_status' => 'settlement',
                'should_redirect' => true,
                'callback_received' => true,
                'force_reload' => true
            ]);
        }
        
        // Return current status without making external API calls
        // (We rely on Midtrans callbacks to update the status)
        return response()->json([
            'status' => 'success',
            'payment_status' => $order->payment_status,
            'transaction_status' => $order->payment_status,
            'should_redirect' => $order->payment_status === 'paid',
            'callback_received' => false,
            'force_reload' => false
        ]);
    }

    /**
     * Handle payment success
     */
    public function success($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        $siteName = ConfigHelper::siteName();

        return Inertia::render('Payment/Success', [
            'title' => 'Payment Success - ' . $siteName,
            'order' => [
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'total_amount' => (int) ceil($order->total_amount),
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at,
            ]
        ]);
    }

    /**
     * Handle payment failure
     */
    public function failed($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        $siteName = ConfigHelper::siteName();

        return Inertia::render('Payment/Failed', [
            'title' => 'Payment Failed - ' . $siteName,
            'order' => [
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'total_amount' => (int) ceil($order->total_amount),
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at,
            ]
        ]);
    }

    /**
     * Extract payment instructions from payment data
     */
    private function extractPaymentInstructionsFromPayment(Payment $payment)
    {
        $gatewayResponse = $payment->gateway_response;
        
        if (!$gatewayResponse) {
            return null;
        }

        $paymentType = $gatewayResponse['payment_type'] ?? null;
        $instructions = [];

        switch ($paymentType) {
            case 'bank_transfer':
                if (isset($gatewayResponse['va_numbers'][0])) {
                    $vaNumber = $gatewayResponse['va_numbers'][0];
                    $instructions = [
                        'type' => 'virtual_account',
                        'bank' => $vaNumber['bank'],
                        'va_number' => $vaNumber['va_number'],
                        'instructions' => $this->getVAInstructions($vaNumber['bank'])
                    ];
                }
                break;

            case 'echannel':
                $instructions = [
                    'type' => 'mandiri_echannel',
                    'bill_key' => $gatewayResponse['bill_key'] ?? null,
                    'biller_code' => $gatewayResponse['biller_code'] ?? null,
                    'instructions' => $this->getMandiriInstructions()
                ];
                break;

            case 'qris':
                $instructions = [
                    'type' => 'qris',
                    'qr_string' => $gatewayResponse['qr_string'] ?? null,
                    'instructions' => $this->getQrisInstructions()
                ];
                break;

            case 'gopay':
            case 'shopeepay':
            case 'dana':
            case 'linkaja':
            case 'ovo':
                $instructions = [
                    'type' => 'ewallet',
                    'provider' => $paymentType,
                    'actions' => $gatewayResponse['actions'] ?? [],
                    'instructions' => $this->getEwalletInstructions($paymentType)
                ];
                break;
        }

        return $instructions;
    }

    /**
     * Map Midtrans status to payment status
     */
    private function mapMidtransStatusToPaymentStatus($transactionStatus, $fraudStatus = null)
    {
        return match($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel' => 'cancelled',
            'expire' => 'expired',
            'failure' => 'failed',
            default => 'pending'
        };
    }

    // Helper methods for instructions (same as in MidtransService)
    private function getVAInstructions($bank)
    {
        $instructions = [
            'bca' => [
                '1. Pilih menu m-BCA di aplikasi BCA mobile',
                '2. Pilih m-Transfer > BCA Virtual Account',
                '3. Masukkan nomor Virtual Account',
                '4. Masukkan jumlah yang harus dibayar',
                '5. Ikuti instruksi untuk menyelesaikan pembayaran'
            ],
            'bni' => [
                '1. Masuk ke ATM BNI atau BNI Mobile Banking',
                '2. Pilih Menu Transfer > Virtual Account Billing',
                '3. Masukkan nomor Virtual Account',
                '4. Masukkan jumlah yang harus dibayar',
                '5. Ikuti instruksi untuk menyelesaikan pembayaran'
            ],
            'bri' => [
                '1. Masuk ke ATM BRI atau BRI Mobile Banking',
                '2. Pilih Menu Pembayaran > BRIVA',
                '3. Masukkan nomor Virtual Account',
                '4. Masukkan jumlah yang harus dibayar',
                '5. Ikuti instruksi untuk menyelesaikan pembayaran'
            ],
            'permata' => [
                '1. Masuk ke Permata Mobile X atau ATM Permata',
                '2. Pilih Menu Transfer > Virtual Account',
                '3. Masukkan nomor Virtual Account',
                '4. Masukkan jumlah yang harus dibayar',
                '5. Ikuti instruksi untuk menyelesaikan pembayaran'
            ]
        ];

        return $instructions[$bank] ?? ['Ikuti instruksi pembayaran melalui ' . strtoupper($bank)];
    }

    private function getMandiriInstructions()
    {
        return [
            '1. Masuk ke ATM Mandiri',
            '2. Pilih Menu Bayar/Beli > Multi Payment',
            '3. Masukkan Kode Perusahaan (Biller Code)',
            '4. Masukkan Kode Pembayaran (Bill Key)',
            '5. Ikuti instruksi untuk menyelesaikan pembayaran'
        ];
    }

    private function getQrisInstructions()
    {
        return [
            '1. Buka aplikasi e-wallet atau mobile banking yang mendukung QRIS',
            '2. Pilih fitur Scan QR atau bayar dengan QRIS',
            '3. Scan kode QR yang ditampilkan',
            '4. Pastikan nominal pembayaran sesuai',
            '5. Konfirmasi pembayaran'
        ];
    }

    private function getEwalletInstructions($provider)
    {
        $instructions = [
            'gopay' => [
                '1. Buka aplikasi Gojek atau GoPay',
                '2. Pilih fitur Bayar atau Pay',
                '3. Klik link pembayaran atau scan QR code',
                '4. Masukkan PIN GoPay',
                '5. Pembayaran berhasil'
            ],
            'shopeepay' => [
                '1. Buka aplikasi Shopee',
                '2. Pilih ShopeePay',
                '3. Klik link pembayaran atau scan QR code',
                '4. Masukkan PIN ShopeePay',
                '5. Pembayaran berhasil'
            ],
            'dana' => [
                '1. Buka aplikasi DANA',
                '2. Pilih fitur Bayar atau Pay',
                '3. Klik link pembayaran atau scan QR code',
                '4. Masukkan PIN DANA',
                '5. Pembayaran berhasil'
            ]
        ];

        return $instructions[$provider] ?? ['Ikuti instruksi pembayaran melalui ' . strtoupper($provider)];
    }
}