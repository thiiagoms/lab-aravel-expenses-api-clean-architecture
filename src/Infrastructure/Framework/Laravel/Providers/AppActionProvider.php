<?php

declare(strict_types=1);

namespace Src\Infrastructure\Framework\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Application\UseCases\Auth\Authenticate\AuthenticateAction;
use Src\Application\UseCases\Auth\Authenticate\Interfaces\AuthenticateActionInterface;
use Src\Application\UseCases\Auth\Authenticate\Interfaces\CheckUserCredentialsInterface;
use Src\Application\UseCases\Auth\Authenticate\Validators\CheckUserCredentials;
use Src\Application\UseCases\User\Find\FindUserByIdAction;
use Src\Application\UseCases\User\Find\Interface\FindUserByIdActionInterface;
use Src\Application\UseCases\User\Register\ConfirmUserEmailAction;
use Src\Application\UseCases\User\Register\Interfaces\ConfirmUserEmailActionInterface;
use Src\Application\UseCases\User\Register\Interfaces\RegisterUserActionInterface;
use Src\Application\UseCases\User\Register\Interfaces\VerifyUserEmailIsAvailableInterface;
use Src\Application\UseCases\User\Register\RegisterUserAction;
use Src\Application\UseCases\User\Update\Interfaces\UpdateUserActionInterface;
use Src\Application\UseCases\User\Update\UpdateUserAction;
use Src\Application\UseCases\User\Validators\VerifyUserEmailIsAvailable;

class AppActionProvider extends ServiceProvider
{
    /**
     * Requests services.
     */
    public function register(): void
    {
        // TODO: This should be move to a dedicated action provider but for now we will keep it here
        $actions = [
            // User
            VerifyUserEmailIsAvailableInterface::class => VerifyUserEmailIsAvailable::class,
            RegisterUserActionInterface::class => RegisterUserAction::class,
            ConfirmUserEmailActionInterface::class => ConfirmUserEmailAction::class,
            FindUserByIdActionInterface::class => FindUserByIdAction::class,
            UpdateUserActionInterface::class => UpdateUserAction::class,
            // Auth
            AuthenticateActionInterface::class => AuthenticateAction::class,
            CheckUserCredentialsInterface::class => CheckUserCredentials::class,
        ];

        foreach ($actions as $interface => $action) {
            $this->app->bind($interface, $action);
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
