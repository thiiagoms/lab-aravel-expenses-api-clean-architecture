<?php

namespace Src\Interfaces\Http\Api\V1\User\Swagger\Requests\Update;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Update user data request with PUT/PATCH HTTP methods',
    description: 'Payload to partial/fully update the authenticated user data. For PUT request all fields are required.',
    type: 'object',
    example: [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'StrongP@ss123!',
    ]
)]
class UpdateUserSwaggerRequest
{
    #[OA\Property(
        property: 'name',
        description: 'The full name of the user.',
        type: 'string',
        maxLength: 150,
        minLength: 3,
        example: 'Jane Doe'
    )]
    public string $name;

    #[OA\Property(
        property: 'email',
        description: 'A valid email address.',
        type: 'string',
        format: 'email',
        example: 'jane@example.com'
    )]
    public string $email;

    #[OA\Property(
        property: 'password',
        description: 'Password with at least one uppercase letter, one lowercase letter, one number, and one special character.',
        type: 'string',
        minLength: 8,
        pattern: '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$',
        example: 'StrongP@ss123!'
    )]
    public string $password;
}
