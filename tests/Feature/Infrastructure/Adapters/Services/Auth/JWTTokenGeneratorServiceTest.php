<?php

namespace Feature\Infrastructure\Adapters\Services\Auth;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Src\Domain\User\Entities\User;
use Src\Infrastructure\Adapters\Services\Auth\JWTTokenGeneratorService;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class JWTTokenGeneratorServiceTest extends TestCase
{
    private User $user;

    private JWTGuard $guard;

    private MockObject $factory;

    private AuthFactory $authFactory;

    private JWTTokenGeneratorService $service;

    protected function setUp(): void
    {

    }
}
