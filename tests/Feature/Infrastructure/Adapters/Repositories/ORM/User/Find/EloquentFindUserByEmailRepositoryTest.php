<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Adapters\Repositories\ORM\User\Find;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\User\ValueObjects\Email;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Find\EloquentFindUserByEmailRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

final class EloquentFindUserByEmailRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private Email $email;

    private EloquentFindUserByEmailRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->email = new Email('ilovelaravel@gmail.com');

        $this->repository = new EloquentFindUserByEmailRepository;
    }

    #[Test]
    public function it_should_return_user_as_user_entity_when_user_with_provided_email_exists_in_database(): void
    {
        LaravelUserModel::factory()->createOne(['email' => $this->email]);

        $result = $this->repository->find($this->email);

        $this->assertEquals($result->email()->getValue(), $this->email->getValue());
    }

    #[Test]
    public function it_should_return_null_when_user_with_provided_email_does_not_exist_in_database(): void
    {
        $result = $this->repository->find($this->email);

        $this->assertNull($result);
    }
}
