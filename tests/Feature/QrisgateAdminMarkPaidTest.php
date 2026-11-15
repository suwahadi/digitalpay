<?php

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Jobs\ProcessDigitalDelivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class QrisgateAdminMarkPaidTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $gateway = Order::QRISGATE): Order
    {
        $method = PaymentMethod::create([
            'code' => 'qris', 'name' => 'QRIS', 'type' => 'qris', 'provider' => 'midtrans',
            'min_amount' => 0, 'is_active' => true, 'sort_order' => 1, 'fee_amount' => 0,
        ]);

        $order = Order::forceCreate([
            'uuid' => (string) Str::uuid(),
            'order_number' => 'DIG-'.Str::upper(Str::random(7)),
            'customer_name' => 'Pembeli',
            'customer_email' => 'pembeli@digitalpay.web.id',
            'payment_method_id' => $method->id,
            'payment_gateway' => $gateway,
            'subtotal' => 50000,
            'total_amount' => 50007,
            'status' => 'pending',
            'payment_status' => 'pending',
            'expires_at' => now()->addMinutes(30),
        ]);

        Payment::forceCreate([
            'uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'amount' => 50007,
            'total_amount' => 50007,
            'customer_type' => 'guest',
            'customer_email' => $order->customer_email,
            'gateway_provider' => $gateway,
            'gateway_transaction_id' => 'trx_admin',
            'status' => Payment::STATUS_PENDING,
        ]);

        return $order;
    }

    public function test_admin_can_mark_qrisgate_order_paid(): void
    {
        Notification::fake();
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order();

        $this->actingAs($admin);
        Livewire::test(ListOrders::class)
            ->assertTableActionVisible('mark_paid', $order)
            ->callTableAction('mark_paid', $order);

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(Payment::STATUS_SUCCESS, $order->payment->fresh()->status);
        $this->assertSame('qrisgate_admin_paid', PaymentLog::sole()->event_type);
        Queue::assertPushed(ProcessDigitalDelivery::class, 1);
    }

    public function test_action_hidden_for_midtrans_orders(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $order = $this->order('midtrans');

        Livewire::test(ListOrders::class)->assertTableActionHidden('mark_paid', $order);
    }
}
