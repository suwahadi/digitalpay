<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $attributes = []): Order
    {
        return Order::forceCreate(array_merge([
            'uuid' => (string) Str::uuid(),
            'order_number' => 'DIG-TEST001',
            'customer_name' => 'Pembeli',
            'customer_email' => 'pembeli@digitalpay.web.id',
            'subtotal' => 10000,
            'total_amount' => 10000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addMinutes(15),
        ], $attributes));
    }

    public function test_stranger_is_sent_to_verification_instead_of_seeing_the_invoice(): void
    {
        $this->makeOrder();

        $this->get('/invoice/DIG-TEST001')->assertRedirect('/order/DIG-TEST001/verify');
        $this->get('/payment/DIG-TEST001')->assertRedirect('/order/DIG-TEST001/verify');
        $this->getJson('/api/invoice/DIG-TEST001/status')->assertForbidden();
        $this->getJson('/invoice/DIG-TEST001/refresh-deliveries')->assertForbidden();
    }

    public function test_wrong_email_is_rejected_and_matching_email_unlocks_the_order(): void
    {
        $this->makeOrder();

        $this->from('/order/DIG-TEST001/verify')
            ->post('/order/DIG-TEST001/verify', ['email' => 'orang-lain@contoh.com'])
            ->assertSessionHasErrors('email');
        $this->getJson('/api/invoice/DIG-TEST001/status')->assertForbidden();

        $this->post('/order/DIG-TEST001/verify', ['email' => ' Pembeli@DigitalPay.web.id '])
            ->assertRedirect('/invoice/DIG-TEST001');
        $this->getJson('/api/invoice/DIG-TEST001/status')->assertOk();
    }

    public function test_verification_is_rate_limited(): void
    {
        $this->makeOrder();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/order/DIG-TEST001/verify', ['email' => "salah{$i}@contoh.com"]);
        }

        $this->post('/order/DIG-TEST001/verify', ['email' => 'pembeli@digitalpay.web.id'])
            ->assertSessionHasErrors('email');
        $this->getJson('/api/invoice/DIG-TEST001/status')->assertForbidden();
    }

    public function test_owner_and_admin_can_open_the_order_but_another_member_cannot(): void
    {
        $owner = User::factory()->create();
        $this->makeOrder(['user_id' => $owner->id]);

        $this->actingAs($owner)->getJson('/api/invoice/DIG-TEST001/status')->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->getJson('/api/invoice/DIG-TEST001/status')->assertOk();
        $this->actingAs(User::factory()->create())->getJson('/api/invoice/DIG-TEST001/status')->assertForbidden();
    }

    public function test_password_reset_token_older_than_an_hour_is_rejected(): void
    {
        $user = User::factory()->create();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('token-lama'),
            'created_at' => now()->subMinutes(61),
        ]);

        $this->get('/reset-password?token=token-lama&email=' . urlencode($user->email))
            ->assertRedirect('/forgot-password');
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }
}
