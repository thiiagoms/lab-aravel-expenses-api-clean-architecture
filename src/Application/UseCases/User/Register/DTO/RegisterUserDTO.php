<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register\DTO;

use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Infrastructure\Support\Sanitizer;
use Src\Interfaces\Http\Api\V1\User\Requests\Register\RegisterUserApiRequest;

final readonly class RegisterUserDTO
{
    public function __construct(private Name $name, private Email $email, private Password $password) {}

    public function name(): Name
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public static function fromRequest(RegisterUserApiRequest $request): self
    {
        $payload = Sanitizer::clean($request->validated());

        return new self(
            name: new Name($payload['name']),
            email: new Email($payload['email']),
            password: new Password($payload['password'])
        );
    }
}
