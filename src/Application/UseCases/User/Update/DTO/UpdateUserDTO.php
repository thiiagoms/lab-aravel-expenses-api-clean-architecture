<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Update\DTO;

use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Support\Sanitizer;
use Src\Interfaces\Http\Api\V1\User\Requests\Update\UpdateUserApiRequest;

final readonly class UpdateUserDTO
{
    public function __construct(
        private Id $id,
        private ?Name $name = null,
        private ?Email $email = null,
        private ?Password $password = null,
    ) {}

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): ?Name
    {
        return $this->name;
    }

    public function email(): ?Email
    {
        return $this->email;
    }

    public function password(): ?Password
    {
        return $this->password;
    }

    public static function fromRequest(UpdateUserApiRequest $request): self
    {
        $payload = Sanitizer::clean($request->validated());

        return new self(
            id: $request->user('api')->id,
            name: isset($payload['name']) ? new Name($payload['name']) : null,
            email: isset($payload['email']) ? new Email($payload['email']) : null,
            password: isset($payload['password']) ? new Password($payload['password']) : null
        );
    }
}
