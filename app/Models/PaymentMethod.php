<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'provider',
        'icon',
        'fee_type',
        'fee_amount',
        'min_amount',
        'max_amount',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'settings' => 'json',
    ];

    public function getLogoUrlAttribute()
    {
        return $this->getIconUrl();
    }

    /**
     * Seeded icons are public paths like /images/payment/bca.svg; icons uploaded
     * from the admin panel are relative paths on the public disk.
     */
    public function getIconUrl()
    {
        if (!$this->icon) {
            return null;
        }

        if (str_starts_with($this->icon, '/') || str_starts_with($this->icon, 'http')) {
            return $this->icon;
        }

        return Storage::url($this->icon);
    }

    public function calculateFee($amount)
    {
        if ($this->fee_type === 'fixed') {
            return (float) ($this->fee_amount ?? 0);
        } elseif ($this->fee_type === 'percentage') {
            // For percentage type, use fee_amount field as percentage value
            return ((float) $amount * (float) ($this->fee_amount ?? 0)) / 100;
        }
        
        return 0;
    }

    public function getTotalAmount($amount)
    {
        return $amount + $this->calculateFee($amount);
    }

    /**
     * Get payment method configuration for Core API
     */
    public function getCoreApiConfig()
    {
        $config = [
            'type' => $this->type,
            'provider' => $this->provider,
            'settings' => $this->settings ?: []
        ];

        switch ($this->code) {
            case 'qris':
                $config['payment_type'] = 'qris';
                $config['display_name'] = 'QRIS';
                $config['logo'] = $this->getIconUrl();
                break;
            case 'gopay':
                $config['payment_type'] = 'gopay';
                $config['display_name'] = 'GoPay';
                $config['logo'] = $this->getIconUrl();
                $config['gopay'] = ['enable_callback' => true];
                break;
            case 'shopeepay':
                $config['payment_type'] = 'shopeepay';
                $config['display_name'] = 'ShopeePay';
                $config['logo'] = $this->getIconUrl();
                $config['shopeepay'] = ['callback_url' => url('/midtrans/notification')];
                break;
            case 'bca_va':
                $config['payment_type'] = 'bank_transfer';
                $config['bank'] = 'bca';
                $config['display_name'] = 'BCA Virtual Account';
                $config['logo'] = $this->getIconUrl();
                break;
            case 'bni_va':
                $config['payment_type'] = 'bank_transfer';
                $config['bank'] = 'bni';
                $config['display_name'] = 'BNI Virtual Account';
                $config['logo'] = $this->getIconUrl();
                break;
            case 'bri_va':
                $config['payment_type'] = 'bank_transfer';
                $config['bank'] = 'bri';
                $config['display_name'] = 'BRI Virtual Account';
                $config['logo'] = $this->getIconUrl();
                break;
            default:
                $config['payment_type'] = $this->type;
                $config['display_name'] = $this->name;
                $config['logo'] = $this->getIconUrl();
        }

        return $config;
    }

    public function getDescription()
    {
        $descriptions = [
            'qris' => 'Bayar dengan scan QR Code menggunakan aplikasi e-wallet',
            'gopay' => 'Bayar dengan GoPay melalui aplikasi Gojek',
            'dana' => 'Bayar dengan DANA e-wallet',
            'shopeepay' => 'Bayar dengan ShopeePay melalui aplikasi Shopee',
            'bca_va' => 'Transfer ke nomor Virtual Account BCA',
            'bni_va' => 'Transfer ke nomor Virtual Account BNI',
            'bri_va' => 'Transfer ke nomor Virtual Account BRI',
        ];

        return $descriptions[$this->code] ?? 'Metode pembayaran ' . $this->name;
    }

    public function isInstantPayment()
    {
        $instantMethods = ['qris', 'gopay', 'dana', 'shopeepay'];
        return in_array($this->code, $instantMethods);
    }

    public function getEstimatedPaymentTime()
    {
        $times = [
            'qris' => 'Instan',
            'gopay' => 'Instan',
            'dana' => 'Instan',
            'shopeepay' => 'Instan',
            'bca_va' => '1-5 menit',
            'bni_va' => '1-5 menit',
            'bri_va' => '1-5 menit',
        ];

        return $times[$this->code] ?? '1-5 menit';
    }

    /**
     * Check if payment method requires manual confirmation
     */
    public function requiresManualConfirmation()
    {
        return in_array($this->type, ['virtual_account', 'bank_transfer']);
    }
}
