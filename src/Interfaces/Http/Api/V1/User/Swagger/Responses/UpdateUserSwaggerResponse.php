<?php

namespace Src\Interfaces\Http\Api\V1\User\Swagger\Responses;

use OpenApi\Attributes as OA;

/**
 * @codeCoverageIgnore
 */
#[OA\Schema(
    title: 'Update user response',
    description: 'Response returned after a successful user update.',
    type: 'object',
    example: [
        'id' => '9f4b7aa8-124a-4c4a-95b9-6d97b7dd63c1',
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'created_at' => '2023-09-01T12:00:00Z',
        'updated_at' => '2023-09-10T14:30:00Z',
    ]
)]
class UpdateUserSwaggerResponse
{
    #[OA\Property(
        property: 'id',
        description: 'Unique identifier of the user.',
        type: 'string',
        format: 'uuid',
        example: '9f4b7aa8-124a-4c4a-95b9-6d97b7dd63c1'
    )]
    public string $id;

    #[OA\Property(
        property: 'name',
        description: 'Full name of the user.',
        type: 'string',
        example: 'Jane Doe'
    )]
    public string $name;

    #[OA\Property(
        property: 'email',
        description: 'Email address of the user.',
        type: 'string',
        format: 'email',
        example: 'jane@example.com'
    )]
    public string $email;

    #[OA\Property(
        property: 'created_at',
        description: 'Timestamp when the user was created.',
        type: 'string',
        format: 'date-time',
        example: '2023-09-01T12:00:00Z'
    )]
    public string $created_at;

    #[OA\Property(
        property: 'updated_at',
        description: 'Timestamp when the user was last updated.',
        type: 'string',
        format: 'date-time',
        example: '2023-09-10T14:30:00Z'
    )]
    public string $updated_at;
}
