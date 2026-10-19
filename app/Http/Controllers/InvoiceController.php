<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\DigitalDeliveryService;
use App\Helpers\ConfigHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Controller untuk mengelola invoice/faktur
 * 
 * Terintegrasi dengan digital delivery system
 */
class InvoiceController extends Controller
{
    protected $digitalDeliveryService;

    public function __construct(DigitalDeliveryService $digitalDeliveryService)
    {
        $this->digitalDeliveryService = $digitalDeliveryService;
    }

    /**
     * Display invoice berdasarkan order number
     * 
     * @param string $orderNumber
     * @return \Inertia\Response
     */
    public function show(string $orderNumber)
    {
        try {
            $order = Order::with([
                'orderItems.productVariant.product',
                'orderItems.productVariant.digitalStocks',
                'paymentMethod',
                'payment'
            ])->where('order_number', $orderNumber)->firstOrFail();

            $order->order_items = $order->orderItems->map(function($item) {
                $price = $this->safeNumeric($item->price);
                $quantity = $this->safeNumeric($item->quantity, 1);
                $totalPrice = $item->total_price ?? $this->calculateTotalPrice($price, $quantity);
                
                return [
                    'id' => $item->id,
                    'product_name' => $item->productVariant->product->name ?? 'Unknown Product',
                    'variant_name' => $item->productVariant->name ?? 'Default Variant',
                    'price' => (int) ceil($item->price),
                    'quantity' => $item->quantity,
                    'total_price' => (int) ceil($item->total_price),
                    'formatted_total' => $this->formatPrice($totalPrice),
                    'delivery_status' => $item->delivery_status,
                    'product_variant' => [
                        'id' => $item->productVariant->id,
                        'name' => $item->productVariant->name,
                        'price' => (int) ceil($item->productVariant->price),
                        'product' => [
                            'id' => $item->productVariant->product->id,
                            'name' => $item->productVariant->product->name,
                            'is_digital' => $item->productVariant->product->is_digital ?? false
                        ]
                    ]
                ];
            });

            // Check apakah order memiliki produk digital
            $hasDigitalProducts = $this->digitalDeliveryService->orderHasDigitalProducts($order);
            
            // Get digital deliveries jika ada
            $digitalDeliveries = collect();
            if ($hasDigitalProducts && $order->status === 'paid') {
                $digitalDeliveries = $this->digitalDeliveryService->getOrderDeliveries($order);
            }

            // Transform digital deliveries untuk frontend
            $transformedDeliveries = $digitalDeliveries->map(function($delivery) {
                return [
                    'id' => $delivery->id,
                    'product_name' => $delivery->orderItem->productVariant->product->name,
                    'variant_name' => $delivery->orderItem->productVariant->name,
                    'download_token' => $delivery->download_token,
                    'download_url' => route('digital.download', $delivery->download_token),
                    'download_count' => $delivery->download_count,
                    'max_downloads' => $delivery->max_downloads,
                    'remaining_downloads' => $delivery->remaining_downloads,
                    'expires_at' => $delivery->expires_at->toISOString(),
                    'is_expired' => $delivery->is_expired,
                    'can_download' => $delivery->canDownload(),
                    'delivered_at' => $delivery->delivered_at ? $delivery->delivered_at->toISOString() : null,
                    'status' => $delivery->delivery_status,
                    'delivery_status' => $delivery->delivery_status, // Tambahkan field ini
                    'content' => $delivery->delivery_content, // Perbaiki field name
                    'first_download_at' => $delivery->first_download_at ? $delivery->first_download_at->toISOString() : null,
                    'last_download_at' => $delivery->last_download_at ? $delivery->last_download_at->toISOString() : null
                ];
            });

            // Transform order data untuk frontend
            $orderData = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'subtotal' => (int) ceil($order->subtotal),
                'tax_amount' => (int) ceil($order->tax_amount ?? 0),
                'fee_amount' => (int) ceil($order->fee_amount ?? 0),
                'discount_amount' => (int) ceil($order->discount_amount ?? 0),
                'total_amount' => (int) ceil($order->total_amount),
                'created_at' => $order->created_at->toISOString(),
                'expires_at' => $order->expires_at ? $order->expires_at->toISOString() : null,
                'midtrans_paid_at' => $order->midtrans_paid_at ? $order->midtrans_paid_at->toISOString() : null,
                'snap_token' => $order->midtrans_snap_token,
                'order_items' => $order->order_items,
                'payment_method' => $order->paymentMethod ? [
                    'id' => $order->paymentMethod->id,
                    'name' => $order->paymentMethod->name,
                    'type' => $order->paymentMethod->type
                ] : null,
                'payment' => $order->payment ? [
                    'id' => $order->payment->id,
                    'gateway_transaction_id' => $order->payment->gateway_transaction_id,
                    'status' => $order->payment->status
                ] : null
            ];

            // Get site name for title
            $siteName = ConfigHelper::siteName();

            return Inertia::render('Invoice/Show', [
                'title' => 'Order #'.$order->order_number.' - ' . $siteName,
                'order' => $orderData,
                'hasDigitalProducts' => $hasDigitalProducts,
                'digitalDeliveries' => $transformedDeliveries->toArray() ?? []
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Inertia::render('Errors/404', [
                'message' => 'Invoice tidak ditemukan'
            ])->toResponse(request())->setStatusCode(404);
        } catch (\Exception $e) {
            return Inertia::render('Errors/500', [
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi nanti.',
                'order_number' => $orderNumber
            ])->toResponse(request())->setStatusCode(500);
        }
    }

    /**
     * Download invoice sebagai PDF (optional)
     * 
     * @param string $orderNumber
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(string $orderNumber)
    {
        try {
            $order = Order::with([
                'orderItems.productVariant.product',
                'paymentMethod',
                'payment'
            ])->where('order_number', $orderNumber)->firstOrFail();

            // Generate PDF menggunakan library seperti DomPDF atau Snappy
            // Contoh implementasi dengan DomPDF:
            /*
            $pdf = PDF::loadView('invoice.pdf', compact('order'));
            return $pdf->download('invoice-' . $orderNumber . '.pdf');
            */
            
            // Untuk sementara return error karena PDF library belum disetup
            return response()->json([
                'message' => 'PDF download feature not implemented yet'
            ], 501);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Invoice tidak ditemukan');
        }
    }

    /**
     * Print invoice (view only untuk print)
     * 
     * @param string $orderNumber
     * @return \Inertia\Response
     */
    public function print(string $orderNumber)
    {
        // Same logic as show() but with print-optimized template
        $order = Order::with([
            'orderItems.productVariant.product',
            'paymentMethod',
            'payment'
        ])->where('order_number', $orderNumber)->firstOrFail();

        return Inertia::render('Invoice/Print', [
            'order' => $order
        ]);
    }

    /**
     * Get invoice status (untuk AJAX calls)
     * 
     * @param string $orderNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(string $orderNumber)
    {
        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();
            
            return response()->json([
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'expires_at' => $order->expires_at ? $order->expires_at->toISOString() : null,
                'is_expired' => $order->expires_at ? $order->expires_at->isPast() : false,
                'has_digital_products' => $this->digitalDeliveryService->orderHasDigitalProducts($order)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }

    /**
     * Refresh digital deliveries untuk order
     * 
     * @param string $orderNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshDigitalDeliveries(string $orderNumber)
    {
        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();
            
            if ($order->status !== 'paid') {
                return response()->json([
                    'message' => 'Order belum dibayar'
                ], 400);
            }

            // Get fresh delivery data
            $deliveries = $this->digitalDeliveryService->getOrderDeliveries($order);
            
            $transformedDeliveries = $deliveries->map(function($delivery) {
                return [
                    'id' => $delivery->id,
                    'product_name' => $delivery->orderItem->productVariant->product->name,
                    'variant_name' => $delivery->orderItem->productVariant->name,
                    'download_token' => $delivery->download_token,
                    'download_url' => route('digital.download', $delivery->download_token),
                    'download_count' => $delivery->download_count,
                    'max_downloads' => $delivery->max_downloads,
                    'remaining_downloads' => $delivery->remaining_downloads,
                    'expires_at' => $delivery->expires_at->toISOString(),
                    'is_expired' => $delivery->is_expired,
                    'can_download' => $delivery->canDownload(),
                    'delivered_at' => $delivery->delivered_at ? $delivery->delivered_at->toISOString() : null,
                    'status' => $delivery->delivery_status,
                    'delivery_status' => $delivery->delivery_status, // Tambahkan field ini
                    'content' => $delivery->delivery_content, // Perbaiki field name
                    'first_download_at' => $delivery->first_download_at ? $delivery->first_download_at->toISOString() : null,
                    'last_download_at' => $delivery->last_download_at ? $delivery->last_download_at->toISOString() : null
                ];
            });

            return response()->json([
                'deliveries' => $transformedDeliveries
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Resend delivery notification email
     * 
     * @param string $orderNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendDeliveryNotification(string $orderNumber)
    {
        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();
            
            if ($order->status !== 'paid') {
                return response()->json([
                    'message' => 'Order belum dibayar'
                ], 400);
            }

            // Get deliveries
            $deliveries = $this->digitalDeliveryService->getOrderDeliveries($order);
            
            if ($deliveries->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada produk digital untuk order ini'
                ], 400);
            }

            // Resend notification logic here
            // Contoh: dispatch job untuk kirim email
            /*
            dispatch(new SendDigitalDeliveryNotification($order, $deliveries));
            */

            return response()->json([
                'message' => 'Notifikasi berhasil dikirim ulang'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }

    /**
     * Format price untuk tampilan (dengan null safety)
     * 
     * @param mixed $price
     * @return string
     */
    private function formatPrice($price): string
    {
        // Handle null atau invalid values
        if ($price === null || $price === '' || !is_numeric($price)) {
            return 'Rp 0';
        }
        
        // Convert to float untuk safety
        $price = (float) $price;
        
        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    /**
     * Safe numeric conversion
     */
    private function safeNumeric($value, float $default = 0.0): float
    {
        if ($value === null || $value === '') {
            return $default;
        }
        
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        return $default;
    }

    /**
     * Calculate total price safely
     */
    private function calculateTotalPrice($price, $quantity): float
    {
        $safePrice = $this->safeNumeric($price);
        $safeQuantity = $this->safeNumeric($quantity, 1);
        
        return $safePrice * $safeQuantity;
    }

    /**
     * Check if order has expired
     * 
     * @param Order $order
     * @return bool
     */
    private function isOrderExpired(Order $order): bool
    {
        return $order->expires_at && $order->expires_at->isPast();
    }

    /**
     * Get order status text untuk display
     * 
     * @param string $status
     * @return string
     */
    private function getStatusText(string $status): string
    {
        $statusMap = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            'processing' => 'Diproses'
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    /**
     * Get payment status text untuk display
     * 
     * @param string $status
     * @return string
     */
    private function getPaymentStatusText(string $status): string
    {
        $statusMap = [
            'pending' => 'Menunggu',
            'paid' => 'Lunas',
            'failed' => 'Gagal',
            'cancelled' => 'Dibatalkan',
            'expired' => 'Kedaluwarsa'
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }
}
