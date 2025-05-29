<?php

declare(strict_types=1);

namespace Src\Infrastructure\Framework\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Application\Interfaces\Mail\SendUserConfirmationEmailInterface;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Infrastructure\Adapters\Transaction\LaravelTransactionManager;
use Src\Infrastructure\Framework\Laravel\Services\Confirm\LaravelSendUserConfirmationEmail;

class AppInfraProvider extends ServiceProvider
{
    /**
     * Requests services.
     */
    public function register(): void
    {
        // TODO: This should be move to a dedicated action provider but for now we will keep it here
        $infraProvider = [
            TransactionManagerInterface::class => LaravelTransactionManager::class,
            SendUserConfirmationEmailInterface::class => LaravelSendUserConfirmationEmail::class,
        ];

        foreach ($infraProvider as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
