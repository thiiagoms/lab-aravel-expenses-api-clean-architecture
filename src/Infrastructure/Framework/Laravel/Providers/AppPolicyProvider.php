<?php

namespace Src\Infrastructure\Framework\Laravel\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Src\Domain\Expense\Entities\Expense;
use Src\Infrastructure\Framework\Laravel\Policies\ExpensePolicy;

class AppPolicyProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(Expense::class, ExpensePolicy::class);
    }
}
