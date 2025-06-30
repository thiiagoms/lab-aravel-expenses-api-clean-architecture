<?php

declare(strict_types=1);

namespace Src\Infrastructure\Framework\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Domain\Repositories\Expense\Find\FindExpenseByIdRepositoryInterface;
use Src\Domain\Repositories\Expense\Register\RegisterExpenseRepositoryInterface;
use Src\Domain\Repositories\User\Find\FindUserByEmailRepositoryInterface;
use Src\Domain\Repositories\User\Find\FindUserByIdRepositoryInterface;
use Src\Domain\Repositories\User\Register\ConfirmUserEmailRepositoryInterface;
use Src\Domain\Repositories\User\Register\RegisterUserRepositoryInterface;
use Src\Domain\Repositories\User\Update\UpdateUserRepositoryInterface;
use Src\Infrastructure\Adapters\Repositories\ORM\Expense\Find\EloquentFindExpenseByIdRepository;
use Src\Infrastructure\Adapters\Repositories\ORM\Expense\Register\EloquentRegisterExpenseRepository;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Find\EloquentFindUserByEmailRepository;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Find\EloquentFindUserByIdRepository;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Register\EloquentConfirmUserEmailRepository;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Register\EloquentRegisterUserRepository;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Update\EloquentUpdateUserRepository;

class AppRepositoryProvider extends ServiceProvider
{
    /**
     * Requests services.
     */
    public function register(): void
    {
        // TODO: This should be move to a dedicated action provider but for now we will keep it here
        $repositories = [
            // User
            FindUserByIdRepositoryInterface::class => EloquentFindUserByIdRepository::class,
            FindUserByEmailRepositoryInterface::class => EloquentFindUserByEmailRepository::class,
            RegisterUserRepositoryInterface::class => EloquentRegisterUserRepository::class,
            ConfirmUserEmailRepositoryInterface::class => EloquentConfirmUserEmailRepository::class,
            UpdateUserRepositoryInterface::class => EloquentUpdateUserRepository::class,
            // Expense
            RegisterExpenseRepositoryInterface::class => EloquentRegisterExpenseRepository::class,
            FindExpenseByIdRepositoryInterface::class => EloquentFindExpenseByIdRepository::class,
        ];

        foreach ($repositories as $interface => $repository) {
            $this->app->bind($interface, $repository);
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
