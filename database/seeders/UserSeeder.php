<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'uuid' => Str::uuid(),
            'name' => 'Administrator',
            'email' => 'admin@digitalpay.web.id',
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $members = [
            [
                'name' => 'Prabu Anandita',
                'email' => 'member@digitalpay.web.id',
                'phone' => '08123456789',
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@digitalpay.web.id',
                'phone' => '081234567891',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@digitalpay.web.id',
                'phone' => '081234567892',
            ],
            [
                'name' => 'Ahmad Rifai',
                'email' => 'ahmad@digitalpay.web.id',
                'phone' => '081234567893',
            ],
        ];

        foreach ($members as $memberData) {
            $member = User::create([
                'uuid' => Str::uuid(),
                'name' => $memberData['name'],
                'email' => $memberData['email'],
                'email_verified_at' => now(),
                'phone' => $memberData['phone'],
                'password' => Hash::make('password123'),
                'role' => 'member',
                'status' => 'active',
            ]);

        }
    }
}
