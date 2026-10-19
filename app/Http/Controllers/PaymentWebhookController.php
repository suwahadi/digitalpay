<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    /**
     * Check if there are any pending payment status updates for the given order
     * This endpoint is called by the frontend to check for Midtrans callback triggers
     */
    public function checkCallbackStatus(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        // Check both session and cache for callback data
        $successCallback = session()->get("payment_callback_{$orderNumber}") 
                         ?? cache()->get("payment_callback_{$orderNumber}");
        
        if ($successCallback) {
            // Clear both session and cache after reading
            session()->forget("payment_callback_{$orderNumber}");
            cache()->forget("payment_callback_{$orderNumber}");
            
            return response()->json([
                'status' => 'success',
                'has_callback' => true,
                'payment_status' => $successCallback['payment_status'],
                'transaction_status' => $successCallback['transaction_status'],
                'should_redirect' => $successCallback['should_redirect'],
                'force_reload' => true,
                'timestamp' => $successCallback['timestamp']
            ]);
        }
        
        // No callback received, return current status
        return response()->json([
            'status' => 'success',
            'has_callback' => false,
            'payment_status' => $order->payment_status,
            'should_redirect' => $order->payment_status === 'paid',
            'force_reload' => false
        ]);
    }
    
    /**
     * Server-Sent Events endpoint for real-time payment updates
     */
    public function streamPaymentStatus(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        // Set headers for Server-Sent Events
        return response()->stream(function () use ($orderNumber) {
            echo "data: " . json_encode([
                'type' => 'connection',
                'message' => 'Connected to payment status stream',
                'order_number' => $orderNumber
            ]) . "\n\n";
            
            // Keep connection alive and check for updates
            $maxTime = 300; // 5 minutes max
            $startTime = time();
            
            while (time() - $startTime < $maxTime) {
                // Check for callback status
                $callbackData = session()->get("payment_callback_{$orderNumber}");
                
                if ($callbackData) {
                    session()->forget("payment_callback_{$orderNumber}");
                    
                    echo "data: " . json_encode([
                        'type' => 'payment_update',
                        'order_number' => $orderNumber,
                        'payment_status' => $callbackData['payment_status'],
                        'should_redirect' => $callbackData['should_redirect'],
                        'force_reload' => true,
                        'timestamp' => $callbackData['timestamp']
                    ]) . "\n\n";
                    
                    break;
                }
                
                // Send heartbeat every 30 seconds
                if (time() % 30 === 0) {
                    echo "data: " . json_encode([
                        'type' => 'heartbeat',
                        'timestamp' => now()->toISOString()
                    ]) . "\n\n";
                }
                
                ob_flush();
                flush();
                sleep(5); // Check every 5 seconds
            }
            
            // Connection timeout
            echo "data: " . json_encode([
                'type' => 'timeout',
                'message' => 'Connection timeout'
            ]) . "\n\n";
            
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ]);
    }
}