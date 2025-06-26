<?php

namespace Src\Interfaces\Http\Api\V1\Auth\Swagger\Requests;

use OpenApi\Attributes as OA;

/**
 * @codeCoverageIgnore
 */
#[OA\Schema(
    title: 'Authenticate user request',
    description: 'Base request for user authentication operation.',
    required: ['email', 'password'],
    type: 'object',
    example: [
        'email' => 'john@example.com',
        'password' => '@p5sSw0rd!',
    ]
)]
class AuthenticateSwaggerRequest
{
    #[OA\Property(
        property: 'email',
        description: 'A valid email address.',
        type: 'string',
        format: 'email',
        example: 'john@example.com',
    )]
    public string $email;

    #[OA\Property(
        property: 'password',
        description: 'Password with at least one uppercase letter, one lowercase letter, one number, and one special character.',
        type: 'string',
        minLength: 8,
        pattern: '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$',
        example: '@p5sSw0rd!'
    )]
    public string $password;
}
