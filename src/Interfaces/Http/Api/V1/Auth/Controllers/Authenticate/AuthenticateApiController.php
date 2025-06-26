<?php

namespace Src\Interfaces\Http\Api\V1\Auth\Controllers\Authenticate;

use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Src\Application\UseCases\Auth\Authenticate\AuthenticateAction;
use Src\Application\UseCases\Auth\Authenticate\DTO\AuthenticateDTO;
use Src\Interfaces\Http\Api\V1\Auth\Requests\Authenticate\AuthenticateRequest;
use Src\Interfaces\Http\Api\V1\Auth\Resources\TokenResource;
use Src\Interfaces\Http\Controller;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateApiController extends Controller
{
    public function __construct(private readonly AuthenticateAction $action) {}

    #[OA\Post(
        path: '/api/v1/auth/login',
        description: 'Authenticate user by providing their email and password. If the credentials are valid, a token is returned which can be used to authenticate subsequent requests.',
        summary: 'Authenticate user and return token',
        requestBody: new OA\RequestBody(
            description: 'User data for login',
            required: true,
            content: new JsonContent(
                ref: '#/components/schemas/AuthenticateSwaggerRequest'
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Successful operation',
                content: new JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/TokenSwaggerResponse',
                        type: 'object'
                    )
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'The server could not process the request due to invalid input.'
            ),
        ],
    )]
    public function __invoke(AuthenticateRequest $request): TokenResource
    {
        $dto = AuthenticateDTO::fromRequest($request);

        $token = $this->action->handle($dto);

        return TokenResource::make($token);
    }
}
