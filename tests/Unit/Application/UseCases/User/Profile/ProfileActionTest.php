<?php

namespace Tests\Unit\Application\UseCases\User\Profile;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Profile\ProfileAction;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class ProfileActionTest extends TestCase
{
    private Id $id;

    private FindOrFailUserByIdService|MockObject $findOrFailUserByIdService;

    private ProfileAction $action;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->id = new Id(fake()->uuid());

        $this->findOrFailUserByIdService = $this->createMock(FindOrFailUserByIdService::class);

        $this->action = new ProfileAction($this->findOrFailUserByIdService);
    }

    #[Test]
    public function it_should_return_user_when_user_with_provided_id_exists(): void
    {
        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willReturn(new User(
                name: new Name('John Doe'),
                email: new Email('ilovelaravel@gmail.com'),
                password: new Password('P4sSW0rd!@#ASD_'),
                id: $this->id
            ));

        $result = $this->action->handle($this->id);

        $this->assertEquals($this->id, $result->id());
        $this->assertEquals('John Doe', $result->name()->getValue());
        $this->assertEquals('ilovelaravel@gmail.com', $result->email()->getValue());
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_provided_id_does_not_exist(): void
    {
        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willThrowException(UserNotFoundException::create('User not found'));

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->action->handle($this->id);
    }
}
