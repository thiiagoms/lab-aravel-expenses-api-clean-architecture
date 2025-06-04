<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Controllers\Register;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Src\Application\UseCases\User\Register\Interfaces\ConfirmUserEmailActionInterface;
use Src\Domain\ValueObjects\Id;
use Src\Interfaces\Http\Api\V1\User\Requests\Register\ConfirmEmailApiRequest;
use Src\Interfaces\Http\Controller;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class ConfirmEmailApiController extends Controller
{
    public function __construct(private readonly ConfirmUserEmailActionInterface $action) {}

    #[Get(
        path: '/api/v1/email-confirmation',
        operationId: 'confirmUserEmail',
        description: 'Confirms a user\'s email address using a signed URL with query parameters.',
        summary: 'Confirm user email',
        tags: ['User'],
        parameters: [
            new Parameter(
                name: 'id',
                description: 'UUID of the user to confirm',
                in: 'query',
                required: true,
                schema: new Schema(type: 'string', format: 'uuid')
            ),
            new Parameter(
                name: 'expires',
                description: 'Expiration timestamp of the signed URL',
                in: 'query',
                required: true,
                schema: new Schema(type: 'integer', format: 'int64')
            ),
            new Parameter(
                name: 'signature',
                description: 'HMAC signature of the URL',
                in: 'query',
                required: true,
                schema: new Schema(type: 'string')
            ),
        ],
        responses: [
            new Response(
                response: HttpResponse::HTTP_OK,
                description: 'Email confirmed successfully',
                content: new JsonContent(
                    properties: [
                        new Property(property: 'message', type: 'string', example: 'Email confirmed successfully.'),
                    ],
                    type: 'object'
                )
            ),
            new Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'Validation error or invalid/expired signature',
                content: new JsonContent(
                    properties: [
                        new Property(property: 'message', type: 'string', example: 'Invalid or expired URL.'),
                        new Property(
                            property: 'errors',
                            type: 'object',
                            example: ['id' => ['The id field is required.']]
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function __invoke(ConfirmEmailApiRequest $request): JsonResponse
    {
        $id = new Id($request->validated()['id']);

        $this->action->handle($id);

        return response()->json(['message' => 'Email confirmed successfully.'], HttpResponse::HTTP_OK);
    }
}
