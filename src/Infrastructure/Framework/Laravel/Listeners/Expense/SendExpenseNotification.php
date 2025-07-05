<?php

namespace Src\Infrastructure\Framework\Laravel\Listeners\Expense;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Src\Domain\Expense\Events\ExpenseWasRegistered;
use Src\Infrastructure\Framework\Laravel\Mail\Expense\LaravelExpenseRegisteredMailable;

class SendExpenseNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(ExpenseWasRegistered $event): void
    {
        $expense = $event->expense;

        Mail::to($event->expense->user()->email()->getValue())
            ->send(new LaravelExpenseRegisteredMailable($expense));
    }
}
