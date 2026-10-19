<?php

namespace Tests\Feature;

use App\Filament\Resources\PaymentMethodResource\Pages\ManagePaymentMethods;
use App\Models\PaymentMethod;
use App\Models\User;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentMethodAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_admin_can_open_the_payment_methods_page(): void
    {
        $this->get('/admin/payment-methods')->assertOk();
    }

    public function test_admin_can_create_a_payment_method(): void
    {
        Livewire::test(ManagePaymentMethods::class)
            ->callAction('create', data: [
                'name' => 'OVO',
                'code' => 'ovo',
                'type' => 'ewallet',
                'provider' => 'midtrans',
                'fee_type' => 'percentage',
                'fee_amount' => 1.5,
                'min_amount' => 0,
                'sort_order' => 9,
                'is_active' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('payment_methods', ['code' => 'ovo', 'type' => 'ewallet']);
    }

    public function test_editing_keeps_the_existing_public_path_icon(): void
    {
        $method = PaymentMethod::create([
            'code' => 'qris', 'name' => 'QRIS', 'type' => 'qris', 'provider' => 'midtrans',
            'icon' => '/images/payment/qris.svg', 'fee_type' => 'percentage', 'fee_amount' => 0.7,
            'min_amount' => 0, 'is_active' => true, 'sort_order' => 1,
        ]);

        Livewire::test(ManagePaymentMethods::class)
            ->callTableAction(EditAction::class, $method, data: ['fee_amount' => 1.0])
            ->assertHasNoTableActionErrors();

        $method->refresh();
        $this->assertSame('/images/payment/qris.svg', $method->icon);
        $this->assertSame('1.00', (string) $method->fee_amount);
    }

    public function test_admin_can_delete_a_payment_method(): void
    {
        $method = PaymentMethod::create([
            'code' => 'dana', 'name' => 'DANA', 'type' => 'ewallet', 'provider' => 'midtrans',
            'min_amount' => 0, 'is_active' => true, 'sort_order' => 2, 'fee_amount' => 0,
        ]);

        Livewire::test(ManagePaymentMethods::class)
            ->callTableAction(TableDeleteAction::class, $method)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('payment_methods', ['id' => $method->id]);
    }
}
