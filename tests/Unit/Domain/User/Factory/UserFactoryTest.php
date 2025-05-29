<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Factory;

use PHPUnit\Framework\Attributes\Test;
use Src\Application\UseCases\User\Register\DTO\RegisterUserDTO;
use Src\Domain\User\Factory\UserFactory;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    #[Test]
    public function it_should_build_user_from_register_user_dto(): void
    {
        $dto = new RegisterUserDTO(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('p4SsWorld!@#@!'),
        );

        $user = UserFactory::fromDTO($dto);

        $this->assertEquals('John Doe', $user->name()->getValue());
        $this->assertEquals('ilovelaravel@gmail.com', $user->email()->getValue());
        $this->assertTrue($user->password()->verifyPasswordMatch('p4SsWorld!@#@!'));
    }
}
