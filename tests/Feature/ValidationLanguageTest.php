<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_messages_are_in_indonesian(): void
    {
        $this->post('/reset-password', [
            'token' => 'x',
            'email' => 'bukan-email',
            'password' => 'abc',
            'password_confirmation' => 'xyz',
        ])->assertSessionHasErrors([
            'email' => 'Email harus berupa alamat email yang valid.',
            'password' => 'Password minimal 6 karakter.',
        ]);

        $this->post('/reset-password', [
            'token' => 'x',
            'email' => 'budi@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret999',
        ])->assertSessionHasErrors(['password' => 'Konfirmasi password tidak cocok.']);
    }
}
