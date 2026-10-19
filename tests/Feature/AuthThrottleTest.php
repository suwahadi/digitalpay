<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_repeated_attempts(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', ['email' => 'salah@contoh.com', 'password' => 'salah'])
                ->assertStatus(302);
        }

        $this->post('/login', ['email' => 'salah@contoh.com', 'password' => 'salah'])
            ->assertStatus(429);
    }

    public function test_forgot_password_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/forgot-password', ['email' => 'salah@contoh.com']);
        }

        $this->post('/forgot-password', ['email' => 'salah@contoh.com'])->assertStatus(429);
    }
}
