<?php

namespace App\Http\Controllers;

use App\Models\DigitalDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller untuk handle secure download file digital
 */
class SecureDownloadController extends Controller
{
    /**
     * Download file digital dengan token
     */
    public function download(Request $request, string $token)
    {
        $delivery = DigitalDelivery::where('download_token', $token)->first();
        
        if (!$delivery) {
            abort(404, 'File not found');
        }
        
        if (!$delivery->canDownload()) {
            if ($delivery->is_expired || $delivery->expires_at->isPast()) {
                abort(410, 'Link Download File sudah expired. Silahkan hubungi Admin');
            }
            
            if ($delivery->download_count >= $delivery->max_downloads) {
                abort(429, 'Batas download telah tercapai');
            }
            
            if ($delivery->delivery_status !== DigitalDelivery::STATUS_COMPLETED) {
                abort(404, 'File belum siap untuk diunduh');
            }

            abort(403, 'Download tidak diizinkan');
        }
        
        $this->logDownloadAttempt($delivery, $request);
        
        $delivery->incrementDownloadCount();
        
        $orderItem = $delivery->orderItem;
        $filename = $this->generateFilename($orderItem, $delivery);
        
        return $this->createDownloadResponse($delivery->delivery_content, $filename);
    }
    
    /**
     * Generate filename untuk download
     */
    protected function generateFilename($orderItem, $delivery): string
    {
        $order = $orderItem->order;
        $product = $orderItem->productVariant->product;
        
        $siteName = \App\Helpers\ConfigHelper::siteName();
        $filename = sprintf(
            '%s_%s_%s.txt',
            str_replace(' ', '_', $siteName),
            str_replace(' ', '_', $product->name),
            $order->order_number
        );
        
        return $filename;
    }
    
    /**
     * Create download response
     */
    protected function createDownloadResponse(string $content, string $filename): StreamedResponse
    {
        return response()->streamDownload(function() use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
    
    /**
     * Log download attempt untuk audit
     */
    protected function logDownloadAttempt(DigitalDelivery $delivery, Request $request): void
    {
    }
    
    /**
     * Check download status (untuk AJAX calls)
     */
    public function checkStatus(string $token)
    {
        $delivery = DigitalDelivery::where('download_token', $token)->first();
        
        if (!$delivery) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        return response()->json([
            'can_download' => $delivery->canDownload(),
            'download_count' => $delivery->download_count,
            'max_downloads' => $delivery->max_downloads,
            'remaining_downloads' => $delivery->remaining_downloads,
            'expires_at' => $delivery->expires_at->toISOString(),
            'is_expired' => $delivery->is_expired
        ]);
    }
}
