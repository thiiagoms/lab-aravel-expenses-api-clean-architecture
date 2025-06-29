<?php

namespace Src\Infrastructure\Framework\Laravel\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Src\Domain\Expense\Events\ExpenseWasRegistered;
use Src\Infrastructure\Framework\Laravel\Listeners\Expense\SendExpenseNotification;

class AppEventProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Event::listen(ExpenseWasRegistered::class, SendExpenseNotification::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
