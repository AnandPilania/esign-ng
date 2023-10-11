<?php

namespace Admin\Providers;


use Admin\Events\OperationLogEvent;
use Admin\Events\UpdateRoleEvent;
use Admin\Listeners\OperationLogListener;
use Admin\Listeners\UpdateRoleListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UpdateRoleEvent::class => [
            UpdateRoleListener::class
        ],
        OperationLogEvent::class => [
            OperationLogListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
