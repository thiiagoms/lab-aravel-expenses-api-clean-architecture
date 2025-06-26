<?php

namespace Src\Interfaces\Http\Api\V1\Auth\Swagger\Responses;

use OpenApi\Attributes as OA;

/**
 * @codeCoverageIgnore
 */
#[OA\Schema(
    title: 'Token for authenticate user request',
    description: 'Response containing authentication token',
    type: 'object',
)]
class TokenSwaggerResponse
{
    #[OA\Property(
        property: 'token',
        description: 'The token for the user.',
        type: 'string',
        example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c'
    )]
    public string $token;

    #[OA\Property(
        property: 'token_type',
        description: 'The type of the token.',
        type: 'string',
        example: 'Bearer'
    )]
    public string $token_type;

    #[OA\Property(
        property: 'expires_in',
        description: 'The expiration time of the token.',
        type: 'integer',
        example: 3600
    )]
    public int $expires_in;
}
