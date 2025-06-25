<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Controllers\Profile;

use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use Src\Application\UseCases\User\Profile\ProfileAction;
use Src\Domain\ValueObjects\Id;
use Src\Interfaces\Http\Api\V1\User\Requests\Profile\ProfileApiRequest;
use Src\Interfaces\Http\Api\V1\User\Resources\UserResource;
use Src\Interfaces\Http\Controller;
use Symfony\Component\HttpFoundation\Response;

final class ProfileApiController extends Controller
{
    public function __construct(private readonly ProfileAction $action) {}

    #[OA\Get(
        path: '/api/v1/user/profile',
        summary: 'Get authenticated user data',
        security: ['bearerAuth'],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'User was found and returned user data successfully',
                content: new JsonContent(ref: '#/components/schemas/UserSwaggerResponse')
            ),
            new OA\Response(
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
        ]
    )]
    public function __invoke(ProfileApiRequest $request): UserResource
    {
        /** @var Id $id */
        $id = $request->user('api')->id;

        $user = $this->action->handle($id);

        return UserResource::make($user);
    }
}
