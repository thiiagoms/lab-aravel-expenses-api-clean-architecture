<?php

declare(strict_types=1);

namespace Feature\Infrastructure\Adapters\Repositories\ORM\User\Find;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Find\EloquentFindUserByIdRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

final class EloquentFindUserByIdRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private Id $id;

    private EloquentFindUserByIdRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = new Id(fake()->uuid());

        $this->repository = new EloquentFindUserByIdRepository;
    }

    #[Test]
    public function it_should_return_user_as_user_entity_when_user_with_provided_id_exists_in_database(): void
    {
        LaravelUserModel::factory()->createOne(['id' => $this->id]);

        $result = $this->repository->find($this->id);

        $this->assertEquals($result->id()->getValue(), $this->id->getValue());
    }

    #[Test]
    public function it_should_return_null_when_user_with_provided_id_does_not_exist_in_database(): void
    {
        $result = $this->repository->find($this->id);

        $this->assertNull($result);
    }
}
