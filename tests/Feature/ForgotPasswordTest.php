<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_reply_does_not_reveal_whether_an_email_is_registered(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'member@contoh.com', 'role' => 'member']);
        User::factory()->create(['email' => 'admin@contoh.com', 'role' => 'admin']);

        $replies = collect(['member@contoh.com', 'admin@contoh.com', 'tidak-ada@contoh.com'])
            ->map(fn (string $email) => $this->from('/forgot-password')
                ->post('/forgot-password', ['email' => $email])
                ->assertRedirect('/forgot-password')
                ->getSession()->get('message'));

        $this->assertCount(1, $replies->unique());
        $this->assertSame('Jika email tersebut terdaftar, link reset password telah dikirim ke email Anda.', $replies->first());
    }
}
