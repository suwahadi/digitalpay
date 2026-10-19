<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\DigitalDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;

class ProcessDigitalDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected Order $order;
    protected string $trigger;     // TAMBAHAN: trigger type
    protected array $options;      // TAMBAHAN: additional options

    // MODIFIKASI: Constructor untuk support trigger type
    public function __construct(Order $order, string $trigger = 'auto', array $options = [])
    {
        $this->order = $order;
        $this->trigger = $trigger;
        $this->options = $options;
    }

    /**
     * Execute the job - MODIFIKASI untuk support manual trigger
     */
    public function handle(DigitalDeliveryService $deliveryService): void
    {
        try {
            // Log::info('=== JOB EXECUTION START ===', [
            //     'order_id' => $this->order->id,
            //     'order_number' => $this->order->order_number,
            //     'trigger' => $this->trigger,
            //     'timestamp' => now()
            // ]);

            // Check apakah order punya digital products
            if (!$deliveryService->orderHasDigitalProducts($this->order)) {
                // Log::info('Order has no digital products, skipping', [
                //     'order_id' => $this->order->id
                // ]);
                return;
            }

            // Log::info('Order has digital products, processing delivery', [
            //     'trigger' => $this->trigger
            // ]);

            // MODIFIKASI: Process berdasarkan trigger type
            if ($this->trigger === 'manual') {
                $results = $deliveryService->processManualDelivery(
                    $this->order, 
                    $this->options['user_id'] ?? null,
                    $this->options['notes'] ?? null
                );
            } else {
                $results = $deliveryService->processOrderDigitalDelivery($this->order);
            }

            // Log::info('Digital delivery processing completed', [
            //     'order_id' => $this->order->id,
            //     'results_count' => count($results),
            //     'results' => $results,
            //     'trigger' => $this->trigger
            // ]);

            // TODO: Send completion email to customer
            // if (count($results) > 0) {
            //     $this->sendDeliveryNotification($results);
            // }

            // Log::info('=== JOB EXECUTION END ===', [
            //     'success_count' => collect($results)->where('status', 'success')->count(),
            //     'total_processed' => count($results),
            //     'trigger' => $this->trigger
            // ]);

        } catch (Exception $e) {
            throw $e;
        }
    }

    // TAMBAHAN: Method untuk send notification (placeholder)
    // private function sendDeliveryNotification(array $results): void
    // {
    //     // TODO: Implement email notification
    //     // Mail::to($this->order->customer_email)
    //     //     ->send(new DigitalDeliveryCompleteMail($this->order, $results, $this->trigger));
    // }

    /**
     * Handle job failure - EXISTING method tetap sama
     */
    public function failed(Exception $exception): void
    {
    }
}
