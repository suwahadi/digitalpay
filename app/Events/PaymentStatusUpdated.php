<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $paymentStatus;
    public $transactionStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, $paymentStatus = null, $transactionStatus = null)
    {
        $this->order = $order;
        $this->paymentStatus = $paymentStatus ?? $order->payment_status;
        $this->transactionStatus = $transactionStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('payment.' . $this->order->order_number),
            new Channel('payment-updates')
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->order->order_number,
            'order_id' => $this->order->id,
            'payment_status' => $this->paymentStatus,
            'transaction_status' => $this->transactionStatus,
            'status' => $this->order->status,
            'should_redirect' => in_array($this->paymentStatus, ['paid', 'settlement']),
            'redirect_url' => in_array($this->paymentStatus, ['paid', 'settlement']) 
                ? "/invoice/{$this->order->order_number}" 
                : null,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'payment.status.updated';
    }
}