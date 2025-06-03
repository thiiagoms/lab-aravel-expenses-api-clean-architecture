<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Auth\Authenticate\DTO;

use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Password;
use Src\Infrastructure\Support\Sanitizer;
use Src\Interfaces\Http\Api\V1\Auth\Requests\Authenticate\AuthenticateRequest;

final readonly class AuthenticateDTO
{
    public function __construct(private Email $email, private Password $password) {}

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public static function fromRequest(AuthenticateRequest $request): self
    {
        $payload = Sanitizer::clean($request->validated());

        return new self(
            email: new Email($payload['email']),
            password: new Password(password: $payload['password'], hashed: false)
        );
    }
}
