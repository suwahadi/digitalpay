<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireOrdersAndPayments extends Command
{
    protected $signature = 'orders:expire';

    protected $description = 'Mark expired orders and payments as expired';

    public function handle()
    {
        $expiredOrdersCount = 0;
        $expiredPaymentsCount = 0;
        $expiredOrderItemsCount = 0;

        try {
            DB::beginTransaction();

            // 1. Expire Orders based on expires_at
            $expiredOrders = Order::whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->whereNotIn('status', ['paid', 'cancelled', 'expired'])
                ->limit(1000)
                ->get();

            foreach ($expiredOrders as $order) {
                $order->update([
                    'status' => 'expired',
                    'payment_status' => 'expired'
                ]);
                
                // Also expire related payments
                // Set expired_at to order's expires_at or current datetime
                $expiredAt = $order->expires_at ?? now();
                $order->payments()->whereNotIn('status', [Payment::STATUS_SUCCESS, Payment::STATUS_CANCELLED, Payment::STATUS_EXPIRED])
                    ->update([
                        'status' => Payment::STATUS_EXPIRED,
                        'expired_at' => $expiredAt
                    ]);
                
                // Also expire related order items (set to 'failed' as per requirement)
                $order->items()->whereNotIn('delivery_status', ['delivered', 'failed'])
                    ->update(['delivery_status' => 'failed']);
                
                $expiredOrdersCount++;
            }

            // 2. Expire Payments based on expired_at (independent payments)
            $expiredPayments = Payment::whereNotNull('expired_at')
                ->where('expired_at', '<=', now())
                ->whereNotIn('status', [Payment::STATUS_SUCCESS, Payment::STATUS_CANCELLED, Payment::STATUS_EXPIRED])
                ->limit(1000)
                ->get();

            foreach ($expiredPayments as $payment) {
                // Update status to expired
                // Note: expired_at is already set (we query whereNotNull('expired_at'))
                // so we keep the original expired_at value
                $payment->update([
                    'status' => Payment::STATUS_EXPIRED,
                ]);

                // Update related order if not already processed
                if ($payment->order && !in_array($payment->order->status, ['paid', 'cancelled', 'expired'])) {
                    $payment->order->update([
                        'status' => 'expired',
                        'payment_status' => 'expired'
                    ]);
                    
                    // Also expire order items (set to 'failed' as per requirement)
                    $payment->order->items()->whereNotIn('delivery_status', ['delivered', 'failed'])
                        ->update(['delivery_status' => 'failed']);
                }
                $expiredPaymentsCount++;
            }

            // 3. Handle orphaned expired order items (items without valid orders/payments)
            $expiredOrderItems = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.expires_at', '<=', now())
                ->where('orders.status', '!=', 'paid')
                ->whereNotIn('order_items.delivery_status', ['delivered', 'failed'])
                ->limit(1000)
                ->get(['order_items.id']);

            if ($expiredOrderItems->count() > 0) {
                $orderItemIds = $expiredOrderItems->pluck('id')->toArray();
                DB::table('order_items')
                    ->whereIn('id', $orderItemIds)
                    ->update(['delivery_status' => 'failed', 'updated_at' => now()]);
                $expiredOrderItemsCount = count($orderItemIds);
            }

            DB::commit();

            if ($expiredOrdersCount > 0 || $expiredPaymentsCount > 0 || $expiredOrderItemsCount > 0) {
                $this->info("Expired {$expiredOrdersCount} orders, {$expiredPaymentsCount} payments, and {$expiredOrderItemsCount} order items.");
            } else {
                $this->info("No expired orders, payments, or order items found.");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error expiring orders/payments: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
