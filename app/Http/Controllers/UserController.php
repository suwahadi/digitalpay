<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Show user dashboard
     */

    /**
     * Show user dashboard
     */
    public function dashboard(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $status = $request->get('status', 'pending');
        
        // Statistics: Transaksi Sukses (nominal orders paid today)
        $successfulTransactionsToday = $user->orders()
            ->where('status', 'paid')
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');
        
        // Statistics: Jumlah Transaksi (count orders this month)
        $transactionCountThisMonth = $user->orders()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        // Statistics: Transaksi Bulan ini (nominal all orders this month)
        $totalTransactionsThisMonth = $user->orders()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');

        // Get orders filtered by status
        $query = $user->orders()
            ->with(['items.product', 'payment'])
            ->latest();

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $recentOrders = $query->take(5)->get();

        // Get order status counts
        $pendingOrders = $user->orders()->where('status', 'pending')->count();
        $paidOrders = $user->orders()->where('status', 'paid')->count();
        $cancelledOrders = $user->orders()->where('status', 'cancelled')->count();
        $expiredOrders = $user->orders()->where('status', 'expired')->count();
        $totalOrders = $user->orders()->count();

        return inertia('User/Dashboard', [
            'user' => $user,
            'currentStatus' => $status,
            'stats' => [
                'totalOrders' => $totalOrders,
                'successfulTransactionsToday' => $successfulTransactionsToday,
                'transactionCountThisMonth' => $transactionCountThisMonth,
                'totalTransactionsThisMonth' => $totalTransactionsThisMonth,
                'pendingOrders' => $pendingOrders,
                'paidOrders' => $paidOrders,
                'cancelledOrders' => $cancelledOrders,
                'expiredOrders' => $expiredOrders,
            ],
            'recentOrders' => $recentOrders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('d M Y H:i'),
                    'items_count' => $order->items->count(),
                    'first_product' => $order->items->first()?->product?->name,
                ];
            }),
        ]);
    }

    /**
     * Show order history with filtering
     */
    public function orderHistory(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status', 'all');
        $perPage = $request->get('per_page', 10);

        $query = $user->orders()
            ->with(['items.product', 'payment', 'items.productVariant'])
            ->latest();

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate($perPage);

        // Get status counts for tabs
        $statusCounts = [
            'all' => $user->orders()->count(),
            'pending' => $user->orders()->where('status', 'pending')->count(),
            'paid' => $user->orders()->where('status', 'paid')->count(),
            'cancelled' => $user->orders()->where('status', 'cancelled')->count(),
            'expired' => $user->orders()->where('status', 'expired')->count(),
        ];

        return inertia('User/OrderHistory', [
            'orders' => $orders,
            'currentStatus' => $status,
            'statusCounts' => $statusCounts,
            'filters' => [
                'status' => $status,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show specific order details
     */
    public function orderDetail($orderNumber)
    {
        $user = Auth::user();
        
        $order = $user->orders()
            ->with(['items.product', 'items.productVariant', 'payment', 'paymentMethod'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return inertia('User/OrderDetail', [
            'order' => $order,
        ]);
    }

    /**
     * Show user profile settings
     */
    public function profile()
    {
        $user = Auth::user();
        
        return inertia('User/Profile', [
            'user' => $user,
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
        ];

        // Add password validation only if password is provided
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $validated = $request->validate($rules);

        // Update basic profile fields
        User::where('id', $user->id)->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            User::where('id', $user->id)->update([
                'password' => Hash::make($validated['password'])
            ]);
        }

        return back()->with('message', 'Profil berhasil diperbarui.');
    }
}