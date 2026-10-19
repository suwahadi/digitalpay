<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\Request;

/**
 * Restricts order-number routes to the admin, the order owner, or a session
 * that created or verified the order. Order numbers are shown and shared,
 * so they must not act as the only secret for invoice and payment data.
 */
class EnsureOrderAccess
{
    public const SESSION_KEY = 'order_access';

    public function handle(Request $request, Closure $next)
    {
        $orderNumber = $request->route('orderNumber');
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order || self::allows($request, $order)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Akses ke pesanan ini ditolak'], 403);
        }

        return redirect()->guest(route('order.verify', $orderNumber));
    }

    public static function allows(Request $request, Order $order): bool
    {
        $user = $request->user();

        if ($user && ($user->isAdmin() || ($order->user_id !== null && (int) $order->user_id === (int) $user->id))) {
            return true;
        }

        return in_array($order->order_number, $request->session()->get(self::SESSION_KEY, []), true);
    }

    public static function grant(Request $request, string $orderNumber): void
    {
        if (!in_array($orderNumber, $request->session()->get(self::SESSION_KEY, []), true)) {
            $request->session()->push(self::SESSION_KEY, $orderNumber);
        }
    }
}
