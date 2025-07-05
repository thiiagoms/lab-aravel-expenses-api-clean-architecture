<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Controllers\Register;

use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use Src\Application\UseCases\User\Register\DTO\RegisterUserDTO;
use Src\Application\UseCases\User\Register\RegisterUserAction;
use Src\Interfaces\Http\Api\V1\User\Requests\Register\RegisterUserApiRequest;
use Src\Interfaces\Http\Api\V1\User\Resources\UserResource;
use Src\Interfaces\Http\Controller;
use Symfony\Component\HttpFoundation\Response;

final class RegisterUserApiController extends Controller
{
    public function __construct(private readonly RegisterUserAction $action) {}

    /**
     * @throws Exception
     */
    #[Post(
        path: '/api/v1/register',
        operationId: 'registerUser',
        description: 'Registers a new user and returns the created user data.',
        summary: 'Requests a new user',
        requestBody: new RequestBody(
            required: true,
            content: new JsonContent(ref: '#/components/schemas/RegisterUserSwaggerRequest')
        ),
        tags: ['User'],
        responses: [
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_CREATED,
                description: 'User successfully registered',
                content: new JsonContent(ref: '#/components/schemas/UserSwaggerResponse')
            ),
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Authentication error or unauthorized error',
                content: new JsonContent(
                    properties: [
                        new Property(
                            property: 'error',
                            type: 'object',
                            example: 'This action is unauthorized.'),
                    ],
                    type: 'object'
                )
            ),
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Validation error',
                content: new JsonContent(
                    properties: [
                        new Property(
                            property: 'error',
                            type: 'object',
                            example: 'Error message about field validation error'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function __invoke(RegisterUserApiRequest $request): JsonResponse
    {
        $dto = RegisterUserDTO::fromRequest($request);

        $user = $this->action->handle($dto);

        return response()->json(
            data: ['data' => UserResource::make($user)],
            status: Response::HTTP_CREATED,
            options: JSON_PRETTY_PRINT
        );
    }
}
