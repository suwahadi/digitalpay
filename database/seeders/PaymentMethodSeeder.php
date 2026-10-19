<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            [
                'code' => 'qris',
                'name' => 'QRIS',
                'type' => 'qris',
                'provider' => 'midtrans',
                'icon' => '/images/payment/qris.png',
                'fee_type' => 'percentage',
                'fee_amount' => 0.7,
                'min_amount' => 1000,
                'sort_order' => 1,
            ],
            [
                'code' => 'gopay',
                'name' => 'GoPay',
                'type' => 'ewallet',
                'provider' => 'midtrans',
                'icon' => '/images/payment/gopay.png',
                'fee_type' => 'percentage',
                'fee_amount' => 2.0,
                'min_amount' => 1000,
                'sort_order' => 2,
            ],
            [
                'code' => 'dana',
                'name' => 'DANA',
                'type' => 'ewallet',
                'provider' => 'midtrans',
                'icon' => '/images/payment/dana.png',
                'fee_type' => 'percentage',
                'fee_amount' => 1.5,
                'min_amount' => 1000,
                'sort_order' => 3,
            ],
            [
                'code' => 'shopeepay',
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'provider' => 'midtrans',
                'icon' => '/images/payment/shopeepay.png',
                'fee_type' => 'percentage',
                'fee_amount' => 1.5,
                'min_amount' => 1000,
                'sort_order' => 4,
            ],
            [
                'code' => 'bca_va',
                'name' => 'BCA Virtual Account',
                'type' => 'virtual_account',
                'provider' => 'midtrans',
                'icon' => '/images/payment/bca.png',
                'fee_type' => 'fixed',
                'fee_amount' => 5000,
                'min_amount' => 10000,
                'sort_order' => 5,
            ],
            [
                'code' => 'bni_va',
                'name' => 'BNI Virtual Account',
                'type' => 'virtual_account',
                'provider' => 'midtrans',
                'icon' => '/images/payment/bni.png',
                'fee_type' => 'fixed',
                'fee_amount' => 4000,
                'min_amount' => 10000,
                'sort_order' => 6,
            ],
            [
                'code' => 'bri_va',
                'name' => 'BRI Virtual Account',
                'type' => 'virtual_account',
                'provider' => 'midtrans',
                'icon' => '/images/payment/bri.png',
                'fee_type' => 'fixed',
                'fee_amount' => 3500,
                'min_amount' => 10000,
                'sort_order' => 7,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}
