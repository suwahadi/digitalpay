<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

/**
 * Guest User class untuk handle notifications untuk guest checkout
 * (order tanpa user account)
 */
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

    /**
     * Route notifications for the database channel.
     * Guest users tidak memiliki database notifications.
     */
    public function routeNotificationForDatabase()
    {
        return null;
    }

    /**
     * Route notifications for the Brevo channel.
     */
    public function routeNotificationForBrevo()
    {
        return $this->email;
    }

    /**
     * Get the email address for notifications.
     */
    public function routeNotificationForMail()
    {
        return $this->email;
    }
}
