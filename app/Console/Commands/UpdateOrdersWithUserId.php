<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\User;
use App\Models\Payment;

class UpdateOrdersWithUserId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-user-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing orders with user_id based on customer email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update orders with user_id...');
        
        // Get orders without user_id but have customer_email
        $orders = Order::whereNull('user_id')
            ->whereNotNull('customer_email')
            ->get();
            
        $this->info("Found {$orders->count()} orders to process");
        
        $updatedOrders = 0;
        $updatedPayments = 0;
        
        foreach ($orders as $order) {
            // Find user by customer email
            $user = User::where('email', $order->customer_email)->first();
            
            if ($user) {
                // Update order with user_id
                $order->update(['user_id' => $user->id]);
                $updatedOrders++;
                
                // Also update associated payment if exists
                $payment = Payment::where('order_id', $order->id)->first();
                if ($payment && !$payment->user_id) {
                    $payment->update([
                        'user_id' => $user->id,
                        'customer_type' => 'registered'
                    ]);
                    $updatedPayments++;
                }
                
                $this->line("Updated order {$order->order_number} for user {$user->email}");
            }
        }
        
        $this->info("Update completed!");
        $this->info("Updated {$updatedOrders} orders");
        $this->info("Updated {$updatedPayments} payments");
        
        return 0;
    }
}
