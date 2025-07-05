<?php

declare(strict_types=1);

namespace Src\Domain\Expense\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Expense\Entities\Expense;

class ExpenseWasRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Expense $expense) {}
}
