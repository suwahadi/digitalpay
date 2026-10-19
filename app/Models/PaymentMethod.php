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

    public function getIconUrl()
    {
        if (!$this->icon) {
            return Storage::url('images/payment/default.png');
        }

        // Check if icon already starts with /storage/ or http
        if (str_starts_with($this->icon, '/storage/') || str_starts_with($this->icon, 'http')) {
            return $this->icon;
        }

        // Otherwise, use Storage::url
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

        // Add specific configurations based on payment method code
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
     * Get Virtual Account display name
     */
    private function getVirtualAccountDisplayName()
    {
        $names = [
            'bca' => 'BCA Virtual Account',
            'bni' => 'BNI Virtual Account',
            'bri' => 'BRI Virtual Account',
            'permata' => 'Permata Virtual Account',
            'cimb' => 'CIMB Niaga Virtual Account'
        ];

        return $names[strtolower($this->provider)] ?? strtoupper($this->provider) . ' Virtual Account';
    }

    /**
     * Get Virtual Account logo
     */
    private function getVirtualAccountLogo()
    {
        if ($this->icon) {
            return Storage::url($this->icon);
        }

        $logos = [
            'bca' => 'images/payment/bca.png',
            'bni' => 'images/payment/bni.png',
            'bri' => 'images/payment/bri.png',
            'permata' => 'images/payment/permata.png',
            'cimb' => 'images/payment/cimb.png'
        ];

        return Storage::url($logos[strtolower($this->provider)] ?? 'images/payment/default.png');
    }

    /**
     * Get E-wallet display name
     */
    private function getEwalletDisplayName()
    {
        $names = [
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'dana' => 'DANA',
            'linkaja' => 'LinkAja',
            'ovo' => 'OVO'
        ];

        return $names[strtolower($this->provider)] ?? strtoupper($this->provider);
    }

    /**
     * Get E-wallet logo
     */
    private function getEwalletLogo()
    {
        if ($this->icon) {
            return Storage::url($this->icon);
        }

        $logos = [
            'gopay' => 'images/payment/gopay.png',
            'shopeepay' => 'images/payment/shopeepay.png',
            'dana' => 'images/payment/dana.png',
            'linkaja' => 'images/payment/linkaja.png',
            'ovo' => 'images/payment/ovo.png'
        ];

        return Storage::url($logos[strtolower($this->provider)] ?? 'images/payment/default.png');
    }

    /**
     * Get Bank Transfer display name
     */
    private function getBankTransferDisplayName()
    {
        $names = [
            'mandiri' => 'Mandiri E-Channel'
        ];

        return $names[strtolower($this->provider)] ?? strtoupper($this->provider) . ' Bank Transfer';
    }

    /**
     * Get Bank Transfer logo
     */
    private function getBankTransferLogo()
    {
        if ($this->icon) {
            return Storage::url($this->icon);
        }

        $logos = [
            'mandiri' => 'images/payment/mandiri.png'
        ];

        return Storage::url($logos[strtolower($this->provider)] ?? 'images/payment/default.png');
    }

    /**
     * Check if payment method requires manual confirmation
     */
    public function requiresManualConfirmation()
    {
        return in_array($this->type, ['virtual_account', 'bank_transfer']);
    }
}
