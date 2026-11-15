<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

class GuestUser
{
    use Notifiable;

    public $email;
    public $name;
    public $phone;

    public function __construct(string $email, string $name = 'Guest', string $phone = null)
    {
        $this->email = $email;
        $this->name = $name;
        $this->phone = $phone;
    }

    public function getKey(): string
    {
        return $this->email;
    }

    public function routeNotificationForDatabase()
    {
        return null;
    }

    public function routeNotificationForBrevo()
    {
        return $this->email;
    }

    public function routeNotificationForMail()
    {
        return $this->email;
    }
}
