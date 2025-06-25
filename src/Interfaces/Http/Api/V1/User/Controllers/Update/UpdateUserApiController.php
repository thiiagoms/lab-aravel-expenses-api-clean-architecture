<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Controllers\Update;

use OpenApi\Attributes as OA;
use Src\Application\UseCases\User\Update\DTO\UpdateUserDTO;
use Src\Application\UseCases\User\Update\UpdateUserAction;
use Src\Interfaces\Http\Api\V1\User\Requests\Update\UpdateUserApiRequest;
use Src\Interfaces\Http\Api\V1\User\Resources\UserResource;
use Src\Interfaces\Http\Controller;
use Symfony\Component\HttpFoundation\Response;

final class UpdateUserApiController extends Controller
{
    public function __construct(private readonly UpdateUserAction $action) {}

    #[OA\Patch(
        path: '/api/v1/user/profile',
        description: 'Allows updating one or more fields of the authenticated user profile.',
        summary: 'Partially Update the authenticated user\'s information',
        security: ['bearerAuth'],
        requestBody: new OA\RequestBody(
            description: 'Payload containing fields to Update the authenticated user.',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserSwaggerRequest')
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'User data successfully updated.',
                content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserSwaggerResponse')
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Bad Request. Validation failed or invalid input.'
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'User not found.'
            ),
        ]
    )]
    #[OA\Put(
        path: '/api/v1/user/profile',
        description: 'Replaces the authenticated user\'s data with the provided information.',
        summary: 'Fully Update the authenticated user\'s information',
        security: ['bearerAuth'],
        requestBody: new OA\RequestBody(
            description: 'Complete payload to replace the authenticated user data.',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserSwaggerRequest')
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'User data successfully replaced.',
                content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserSwaggerResponse')
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Bad Request. Validation failed or invalid input.'
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'User not found.'
            ),
        ]
    )]
    public function update(UpdateUserApiRequest $request): UserResource
    {
        $dto = UpdateUserDTO::fromRequest($request);

        $user = $this->action->handle($dto);

        return UserResource::make($user);
    }
}
