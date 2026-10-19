<?php

namespace Tests\Feature;

use App\Models\GuestUser;
use App\Models\User;
use App\Notifications\EmailVerificationOTP;
use App\Services\BrevoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class BrevoChannelTest extends TestCase
{
    use RefreshDatabase;

    private function enableBrevo(): void
    {
        config([
            'services.brevo.enabled' => true,
            'services.brevo.api_key' => 'xkeysib-test',
            'services.brevo.api_url' => 'https://api.brevo.com/v3',
            'services.brevo.sender_email' => 'no-reply@digitalpay.web.id',
            'services.brevo.sender_name' => 'DigitalPay',
        ]);
        $this->app->forgetInstance(BrevoService::class);
    }

    public function test_failed_brevo_send_throws_so_it_can_be_retried(): void
    {
        $this->enableBrevo();
        Http::fake(['api.brevo.com/*' => Http::response(['message' => 'down'], 500)]);

        $this->expectException(RuntimeException::class);

        (new GuestUser('budi@example.com', 'Budi'))
            ->notifyNow(new EmailVerificationOTP('123456', 'Budi'));
    }

    public function test_successful_brevo_send_does_not_throw(): void
    {
        $this->enableBrevo();
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '<x>'], 201)]);

        (new GuestUser('budi@example.com', 'Budi'))
            ->notifyNow(new EmailVerificationOTP('123456', 'Budi'));

        Http::assertSent(fn ($request) => $request['to'][0]['email'] === 'budi@example.com'
            && $request['sender']['email'] === 'no-reply@digitalpay.web.id');
    }

    public function test_disabled_brevo_is_skipped_quietly(): void
    {
        config(['services.brevo.enabled' => false]);
        Http::fake();

        (new GuestUser('budi@example.com', 'Budi'))
            ->notifyNow(new EmailVerificationOTP('123456', 'Budi'));

        Http::assertNothingSent();
    }

    public function test_registration_succeeds_when_the_otp_email_fails(): void
    {
        $this->enableBrevo();
        Http::fake(['api.brevo.com/*' => Http::response(['message' => 'down'], 500)]);

        $response = $this->post('/register', [
            'name' => 'Budi',
            'username' => 'budi',
            'email' => 'budi@example.com',
            'phone' => '',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/verify?email='.urlencode('budi@example.com'));
        $response->assertSessionHas('message', fn ($m) => str_contains($m, 'gagal terkirim'));
        $this->assertDatabaseHas(User::class, ['email' => 'budi@example.com']);
    }
}
