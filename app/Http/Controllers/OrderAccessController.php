<?php

namespace App\Http\Controllers;

use App\Helpers\ConfigHelper;
use App\Http\Middleware\EnsureOrderAccess;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Lets a guest unlock an order on a new device by confirming the checkout email.
 */
class OrderAccessController extends Controller
{
    public function show(string $orderNumber)
    {
        return Inertia::render('Invoice/Verify', [
            'title' => 'Verifikasi Pesanan - ' . ConfigHelper::siteName(),
            'orderNumber' => $orderNumber,
        ]);
    }

    public function verify(Request $request, string $orderNumber)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $key = 'order-verify:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.',
            ]);
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order || !hash_equals(Str::lower($order->customer_email), Str::lower(trim($request->email)))) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['email' => 'Email tidak cocok dengan pesanan ini.']);
        }

        RateLimiter::clear($key);
        EnsureOrderAccess::grant($request, $order->order_number);

        return redirect()->intended(route('invoice.show', $order->order_number));
    }
}
