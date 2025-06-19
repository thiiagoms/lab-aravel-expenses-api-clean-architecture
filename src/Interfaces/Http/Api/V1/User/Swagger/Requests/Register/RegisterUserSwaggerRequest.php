<?php

namespace Src\Interfaces\Http\Api\V1\User\Swagger\Requests\Register;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Register user request',
    description: 'Base request for user register operation.',
    required: ['name', 'email', 'password'],
    type: 'object',
    example: [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '@p5sSw0rd!',
    ]
)]
class RegisterUserSwaggerRequest
{
    #[OA\Property(
        property: 'name',
        description: 'The full name of the user.',
        type: 'string',
        maxLength: 150,
        minLength: 3,
        example: 'John Doe'
    )]
    public string $name;

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
