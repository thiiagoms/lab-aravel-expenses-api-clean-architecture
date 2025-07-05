<?php

declare(strict_types=1);

namespace Src\Infrastructure\Framework\Laravel\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppRouteProvider extends ServiceProvider
{
    /**
     * Requests services.
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
        $this->mapUserRoutesApiVersionOne();
        $this->mapAuthRoutesApiVersionOne();
        $this->mapExpenseRoutesApiVersionOne();
    }

    private function mapUserRoutesApiVersionOne(): void
    {
        $userRoutesPath = base_path('src/Interfaces/Http/Api/V1/User/Routes/routes.php');

        if (file_exists($userRoutesPath)) {
            Route::prefix('api/v1/')
                ->name('api.v1.')
                ->middleware('api')
                ->group($userRoutesPath);
        }
    }

    private function mapAuthRoutesApiVersionOne(): void
    {
        $authRoutesPath = base_path('src/Interfaces/Http/Api/V1/Auth/Routes/routes.php');

        if (file_exists($authRoutesPath)) {
            Route::prefix('api/v1/auth/')
                ->name('api.v1.auth.')
                ->middleware('api')
                ->group($authRoutesPath);
        }
    }

    private function mapExpenseRoutesApiVersionOne(): void
    {
        $expenseRoutesPath = base_path('src/Interfaces/Http/Api/V1/Expense/Routes/routes.php');

        if (file_exists($expenseRoutesPath)) {
            Route::prefix('api/v1/expense/')
                ->name('api.v1.expense.')
                ->middleware('api')
                ->group($expenseRoutesPath);
        }
    }
}
