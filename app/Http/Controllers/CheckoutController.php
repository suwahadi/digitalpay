<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Helpers\ConfigHelper;
use App\Services\MidtransService;
use App\Http\Middleware\EnsureOrderAccess;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }
    /**
     * Handle direct access to checkout page
     * Redirect to home page when accessed without proper order data
     */
    public function index(Request $request)
    {
        return redirect()->route('home')
            ->with('info', 'Silakan pilih produk terlebih dahulu untuk melanjutkan ke checkout.');
    }
    public function create(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'variant_id' => 'required|exists:product_variants,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('home')
                ->with('error', 'Data produk tidak valid. Silakan pilih produk kembali.');
        }

        $product = Product::with(['category', 'variants'])->findOrFail($request->product_id);
        $selectedVariant = ProductVariant::findOrFail($request->variant_id);
        
        if (!$product || !$selectedVariant) {
            return redirect()->route('home')
                ->with('error', 'Produk atau variant tidak ditemukan.');
        }
    
        if ($selectedVariant->product_id !== $product->id) {
            abort(422, 'Invalid variant for this product');
        }

        if (!$selectedVariant->is_active || $selectedVariant->stock_status === 'Habis') {
            return back()->withErrors(['variant' => 'Selected variant is not available']);
        }

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'code' => $method->code,
                    'type' => $method->type,
                    'provider' => $method->provider,
                    'display_name' => $method->name,
                    'logo' => $method->logo_url,
                    'description' => $this->getPaymentMethodDescription($method),
                    'fee_type' => $method->fee_type ?? 'none',
                    'fee_amount' => (float) ($method->fee_amount ?? 0),
                    'min_amount' => $method->min_amount,
                    'max_amount' => $method->max_amount,
                    'is_instant' => $this->isInstantPayment($method),
                    'estimated_time' => $this->getEstimatedPaymentTime($method),
                ];
            });
        
        $subtotal = (float) ($selectedVariant->price);
        $availableVouchers = \App\Models\Voucher::query()
            ->where('is_active', true)
            ->where(function($q){ $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
            ->where(function($q){ $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()); })
            ->where(function($q) use ($subtotal){ $q->whereNull('min_total')->orWhere('min_total', '<=', $subtotal); })
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($v){ return [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'discount_type' => $v->discount_type,
                'discount_value' => (float) $v->discount_value,
                'max_discount' => (float) ($v->max_discount ?? 0),
                'min_total' => (float) ($v->min_total ?? 0),
                'expires_at' => optional($v->expires_at)->toDateTimeString(),
            ]; });
        $siteName = ConfigHelper::siteName();

        return Inertia::render('Checkout/Create', [
            'title' => 'Checkout',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $product->image_url,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ],
            ],
            'selectedVariant' => [
                'id' => $selectedVariant->id,
                'name' => $selectedVariant->name,
                'price' => $selectedVariant->price,
                'original_price' => $selectedVariant->original_price,
                'auto_delivery' => $selectedVariant->auto_delivery,
                'discount_percentage' => $selectedVariant->discount_percentage,
                'stock_status' => $selectedVariant->stock_status,
            ],
            'paymentMethods' => $paymentMethods,
            'availableVouchers' => $availableVouchers,
            'auth' => [
                'user' => Auth::user() ? [
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'phone' => Auth::user()->phone ?? '',
                ] : null
            ],
        ]);
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'product_id' => 'required|exists:products,id',
                'variant_id' => 'required|exists:product_variants,id',
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'required|string|max:20',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'notes' => 'nullable|string|max:500',
                'voucher_code' => 'nullable|string|max:50',
            ];

            $request->validate($rules);

            DB::beginTransaction();
            
            $product = Product::findOrFail($request->product_id);
            $variant = ProductVariant::findOrFail($request->variant_id);
            $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

            if ($variant->product_id !== $product->id || !$product->is_active || !$variant->is_active || $variant->stock_status === 'Habis') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'general' => 'Produk atau varian ini sedang tidak tersedia. Silakan pilih produk kembali.',
                ]);
            }

            $quantity = 1;
            $subtotal = $variant->price * $quantity;

            $voucher = null;
            $discount = 0;
            if ($request->filled('voucher_code')) {
                $voucher = Voucher::active()->validDate()->where('code', $request->voucher_code)->lockForUpdate()->first();
                if ($voucher && $voucher->isApplicable((float) $subtotal)) {
                    $discount = $voucher->calculateDiscount((float) $subtotal);
                } else {
                    $voucher = null;
                }
            }

            $discount = ceil($discount);
            
            $discountedSubtotal = max($subtotal - $discount, 0);
            $feeAmount = $paymentMethod->calculateFee($discountedSubtotal);
            
            $feeAmount = ceil($feeAmount);
            
            $totalAmount = $discountedSubtotal + $feeAmount;
            $totalAmount = ceil($totalAmount);
            
            $order = Order::create([
                'uuid' => Str::uuid(),
                'order_number' => Order::generateOrderNumber(),
                'user_id' => Auth::id(),
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'payment_method_id' => $request->payment_method_id,
                'voucher_code' => $voucher?->code,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'subtotal' => ceil($subtotal),
                'discount_amount' => $discount,
                'fee_amount' => $feeAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'expires_at' => Carbon::now()->addMinutes(15),
            ]);

            $voucher?->increment('used_count');

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'product_name' => $product->name,
                'variant_name' => $variant->name,
                'quantity' => 1,
                'price' => ceil($variant->price),
                'total' => ceil($subtotal),
            ]);

            DB::commit();

            EnsureOrderAccess::grant($request, $order->order_number);

            // Create Midtrans Core API payment (outside transaction)
            $paymentResult = $this->midtransService->createTransaction($order, $paymentMethod);
            
            if ($paymentResult['success']) {
                try {
                    $payment = \App\Models\Payment::create([
                        'uuid' => \Illuminate\Support\Str::uuid(),
                        'order_id' => $order->id,
                        'user_id' => Auth::id(),
                        'payment_method_id' => $paymentMethod->id,
                        'amount' => ceil($order->total_amount),
                        'fee_amount' => ceil($feeAmount),
                        'total_amount' => ceil($order->total_amount),
                        'currency' => 'IDR',
                        'customer_type' => Auth::check() ? 'registered' : 'guest',
                        'customer_email' => $order->customer_email,
                        'customer_phone' => $order->customer_phone,
                        'gateway_provider' => 'midtrans',
                        'gateway_transaction_id' => $paymentResult['data']['transaction_id'] ?? null,
                        'gateway_response' => json_encode($paymentResult['data']),
                        'status' => 'pending',
                    ]);
                } catch (\Exception $e) {
                    \Log::error('[Checkout] Failed to create payment record', [
                        'error' => $e->getMessage(),
                        'order_number' => $order->order_number,
                    ]);
                }

                session([
                    'payment_instructions' => $paymentResult['payment_instructions'] ?? [],
                    'payment_data' => $paymentResult['payment_data'] ?? [],
                    'checkout_order_number' => $order->order_number,
                ]);

                // Frontend will extract order_number and force redirect to prevent stuck loading
                if ($request->header('X-Inertia')) {
                    return redirect()->route('payment.show', $order->order_number)
                        ->with([
                            'success' => 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.',
                        ]);
                }
                
                return redirect()->away(url('/payment/' . $order->order_number))
                    ->with([
                        'success' => 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.',
                    ]);
            } else {
                $order->update(['status' => 'failed', 'payment_status' => 'failed']);
                
                \Log::error('[Checkout] Payment creation failed', [
                    'order_number' => $order->order_number,
                    'error_message' => $paymentResult['message'] ?? 'Unknown error',
                ]);
                
                return back()->withErrors([
                    'payment' => 'Terjadi kesalahan saat membuat pembayaran: ' . ($paymentResult['message'] ?? 'Unknown error')
                ]);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            \Log::warning('[Checkout] Validation failed', [
                'errors' => $e->errors(),
            ]);
            
            return back()->withErrors($e->errors());
            
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            DB::rollBack();
            
            \Log::error('[Checkout] CSRF token mismatch', [
                'session_id' => $request->session()->getId(),
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors([
                'csrf' => 'Session expired. Please refresh the page and try again.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('[Checkout] Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors([
                'general' => 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get payment method description
     */
    private function getPaymentMethodDescription($method)
    {
        $descriptions = [
            'qris' => 'Bayar dengan scan QR Code menggunakan aplikasi e-wallet',
            'gopay' => 'Bayar dengan GoPay melalui aplikasi Gojek',
            'dana' => 'Bayar dengan DANA e-wallet',
            'shopeepay' => 'Bayar dengan ShopeePay melalui aplikasi Shopee',
            'bca_va' => 'Transfer ke nomor Virtual Account BCA',
            'bni_va' => 'Transfer ke nomor Virtual Account BNI',
            'bri_va' => 'Transfer ke nomor Virtual Account BRI',
        ];

        return $descriptions[$method->code] ?? 'Metode pembayaran ' . $method->name;
    }

    /**
     * Check if payment method is instant
     */
    private function isInstantPayment($method)
    {
        $instantMethods = ['qris', 'gopay', 'dana', 'shopeepay'];
        return in_array($method->code, $instantMethods);
    }

    /**
     * Get estimated payment time
     */
    private function getEstimatedPaymentTime($method)
    {
        $times = [
            'qris' => 'Instan',
            'gopay' => 'Instan',
            'dana' => 'Instan',
            'shopeepay' => 'Instan',
            'bca_va' => '1-5 menit',
            'bni_va' => '1-5 menit',
            'bri_va' => '1-5 menit',
        ];

        return $times[$method->code] ?? '1-5 menit';
    }

}