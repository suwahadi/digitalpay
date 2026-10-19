<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDigitalDelivery;
use App\Models\Order;
use Illuminate\Console\Command;

class ProcessManualDelivery extends Command
{
    protected $signature = 'delivery:manual 
        {order_number : Order number to process}
        {--notes= : Optional notes for the manual delivery}
        {--user_id=1 : User ID of admin processing the delivery}';

    protected $description = 'Manually process digital delivery for an order';

    public function handle()
    {
        $orderNumber = $this->argument('order_number');
        $notes = $this->option('notes');
        $userId = $this->option('user_id');

        $this->info("🔍 Processing manual delivery for order: {$orderNumber}");

        // Find the order
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            $this->error("❌ Order {$orderNumber} not found!");
            return Command::FAILURE;
        }

        // Validate order status
        if ($order->status !== 'paid') {
            $this->error("❌ Order {$orderNumber} is not paid yet! Current status: {$order->status}");
            return Command::FAILURE;
        }

        // Check if order requires manual delivery
        if (!$order->requiresManualDelivery() && !$order->needs_manual_delivery) {
            $this->error("❌ Order {$orderNumber} does not require manual delivery!");
            return Command::FAILURE;
        }

        // Show order details
        $this->displayOrderDetails($order);

        // Confirm processing
        if (!$this->confirm('Do you want to proceed with manual delivery?')) {
            $this->info('Manual delivery cancelled.');
            return Command::SUCCESS;
        }

        try {
            ProcessDigitalDelivery::dispatch($order, 'manual', [
                'user_id' => $userId,
                'notes' => $notes
            ]);

            $this->info("Manual delivery job dispatched successfully!");
            $this->info("Customer will receive email notification once delivery is complete.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error processing manual delivery: {$e->getMessage()}");
            
            return Command::FAILURE;
        }
    }

    protected function displayOrderDetails(Order $order): void
    {
        $this->info("\n📋 ORDER DETAILS:");
        $this->line("   Order Number: {$order->order_number}");
        $this->line("   Customer: {$order->customer_name}");
        $this->line("   Email: {$order->customer_email}");
        $this->line("   Phone: {$order->customer_phone}");
        $this->line("   Total: Rp " . number_format($order->total_amount, 0, ',', '.'));
        $this->line("   Status: {$order->status}");
        $this->line("   Payment Status: {$order->payment_status}");
        
        if ($order->manual_delivery_queued_at) {
            $this->line("   Queued At: {$order->manual_delivery_queued_at}");
        }

        $this->info("\n📦 ORDER ITEMS:");
        foreach ($order->orderItems as $item) {
            $autoDelivery = $item->productVariant->auto_delivery ? 'Auto' : 'Manual';
            $this->line("{$order->order_number} - {$item->product_name} ({$item->variant_name}) - {$autoDelivery} Delivery");
        }

        $this->line("");
    }
}
