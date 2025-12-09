<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class UserActivitySubscriber
{
    /**
     * Handle user login events.
     */
    public function handleUserLogin(Login $event): void {
        if ($event->user) {
            $event->user->update(['is_active' => true]);
        }
    }

    /**
     * Handle user logout events.
     */
    public function handleUserLogout(Logout $event): void {
        if ($event->user) {
            $event->user->update(['is_active' => false]);
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleUserLogin',
            Logout::class => 'handleUserLogout',
        ];
    }
}
