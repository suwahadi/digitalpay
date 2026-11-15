<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Helpers\ConfigHelper;
use App\Services\MidtransService;
use App\Services\QrisgateException;
use App\Services\QrisgateService;
use App\Observers\OrderObserver;
use App\Models\Payment;
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

    protected $qrisgateService;

    public function __construct(MidtransService $midtransService, QrisgateService $qrisgateService)
    {
        $this->midtransService = $midtransService;
        $this->qrisgateService = $qrisgateService;
    }

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

        $paymentMethods = PaymentMethod::available()
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
        $availableVouchers = Voucher::active()
            ->public()
            ->validDate()
            ->where(function ($q) use ($subtotal) {
                $q->whereNull('min_total')->orWhere('min_total', '<=', $subtotal);
            })
            ->orderBy('id', 'desc')
            ->get()
            ->filter(fn (Voucher $v) => $v->hasUsageLeft())
            ->map(fn (Voucher $v) => $this->voucherPayload($v))
            ->values();
        $siteName = ConfigHelper::siteName();

        return Inertia::render('Checkout/Create', [
            'title' => 'Checkout',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $product->thumb_url,
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
                'auto_delivery' => $selectedVariant->isAutoDelivery(),
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

            if (! PaymentMethod::available()->whereKey($paymentMethod->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'payment_method_id' => 'Metode pembayaran ini sedang tidak tersedia.',
                ]);
            }

            $useQrisgate = config('services.payment_gateway') === Order::QRISGATE;

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
                'payment_gateway' => $useQrisgate ? Order::QRISGATE : 'midtrans',
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

            if ($useQrisgate) {
                return $this->startQrisgatePayment($request, $order, $paymentMethod);
            }

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
                $order->updateQuietly(['status' => 'cancelled', 'payment_status' => 'failed']);

                \Log::error('[Checkout] Payment creation failed', [
                    'order_number' => $order->order_number,
                    'error_message' => $paymentResult['message'] ?? 'Unknown error',
                ]);

                return $this->checkoutFailed($request, 'Pembayaran tidak dapat dibuat saat ini. Silakan coba lagi beberapa saat lagi.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            \Log::warning('[Checkout] Validation failed', [
                'errors' => $e->errors(),
            ]);

            return $this->checkoutFailed($request, collect($e->errors())->flatten()->first());
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

            return $this->checkoutFailed($request, 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
        }
    }

    private function startQrisgatePayment(Request $request, Order $order, PaymentMethod $paymentMethod)
    {
        try {
            $data = $this->qrisgateService->createTransaction($order);
        } catch (QrisgateException $e) {
            $order->updateQuietly(['status' => 'cancelled', 'payment_status' => 'failed']);
            $order->items()->update(['delivery_status' => 'failed']);

            \Log::error('[Checkout] QRISGate transaction failed', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return $this->checkoutFailed($request, 'Pembayaran QRIS tidak dapat dibuat saat ini. Silakan coba lagi beberapa saat lagi.');
        }

        $total = (int) $data['total_amount'];
        $expiresAt = ! empty($data['expires_at']) ? Carbon::parse($data['expires_at'])->setTimezone(config('app.timezone')) : $order->expires_at;

        $order->update([
            'unique_code' => isset($data['unique_code']) ? (int) $data['unique_code'] : max($total - (int) round((float) $order->total_amount), 0),
            'total_amount' => $total,
            'payment_status' => 'pending',
            'expires_at' => $expiresAt,
        ]);

        Payment::create([
            'uuid' => Str::uuid(),
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => $total,
            'fee_amount' => ceil($order->fee_amount),
            'total_amount' => $total,
            'currency' => 'IDR',
            'customer_type' => $order->user_id ? 'registered' : 'guest',
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'gateway_provider' => Order::QRISGATE,
            'gateway_transaction_id' => $data['transaction_id'],
            'gateway_response' => $data,
            'status' => Payment::STATUS_PENDING,
            'payment_url' => $data['checkout_url'] ?? null,
            'return_url' => route('invoice.show', $order->order_number),
            'expired_at' => $expiresAt,
        ]);

        app(OrderObserver::class)->notifyOrderCreated($order);

        return redirect()->route('invoice.show', $order->order_number);
    }

    public function checkVoucher(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $voucher = Voucher::active()
            ->validDate()
            ->where('code', trim($data['code']))
            ->first();

        if (! $voucher || ! $voucher->hasUsageLeft()) {
            return response()->json(['message' => 'Voucher tidak valid atau sudah tidak berlaku.'], 422);
        }

        return response()->json(['voucher' => $this->voucherPayload($voucher)]);
    }

    private function voucherPayload(Voucher $voucher): array
    {
        return [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'name' => $voucher->name,
            'discount_type' => $voucher->discount_type,
            'discount_value' => (float) $voucher->discount_value,
            'max_discount' => (float) ($voucher->max_discount ?? 0),
            'min_total' => (float) ($voucher->min_total ?? 0),
            'expires_at' => optional($voucher->expires_at)->toDateTimeString(),
        ];
    }

    private function checkoutFailed(Request $request, string $message)
    {
        $product = Product::find($request->input('product_id'));

        return ($product ? redirect()->route('products.show', $product->slug) : redirect()->route('home'))
            ->with('error', $message);
    }

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

    private function isInstantPayment($method)
    {
        $instantMethods = ['qris', 'gopay', 'dana', 'shopeepay'];
        return in_array($method->code, $instantMethods);
    }

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