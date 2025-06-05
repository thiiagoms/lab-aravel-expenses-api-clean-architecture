<?php

namespace Src\Interfaces\Http\Api\V1\User\Swagger\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'User response',
    description: 'Base response for user CRUD operations',
    type: 'object',
)]
class UserSwaggerResponse
{
    #[OA\Property(
        title: 'Data',
        description: 'The data of the created user.',
        properties: [
            new OA\Property(
                property: 'id',
                title: 'Id',
                description: 'The unique identifier of the user.',
                type: 'string',
                format: 'uuid',
            ),
            new OA\Property(
                property: 'name',
                title: 'Name',
                description: 'The name of the user.',
                type: 'string',
                example: 'John Doe'
            ),
            new OA\Property(
                property: 'email',
                title: 'Email',
                description: 'The email address of the user.',
                type: 'string',
                format: 'email',
                example: 'johndoe@example.com'
            ),
            new OA\Property(
                property: 'created_at',
                title: 'Created at',
                description: 'The date and time when the user was created.',
                type: 'string',
                format: 'date-time',
                example: '2023-01-01 12:00:00'
            ),
            new OA\Property(
                property: 'updated_at',
                title: 'Updated at',
                description: 'The date and time when the user was updated.',
                type: 'string',
                format: 'date-time',
                example: '2023-01-01 12:00:00'
            ),
        ],
        type: 'object'
    )]
    public object $data;
}
