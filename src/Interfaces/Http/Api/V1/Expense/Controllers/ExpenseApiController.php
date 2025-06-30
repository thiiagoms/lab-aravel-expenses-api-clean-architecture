<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\Expense\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;
use Src\Application\UseCases\Expense\Register\DTO\RegisterExpenseDTO;
use Src\Application\UseCases\Expense\Register\RegisterExpenseAction;
use Src\Application\UseCases\Expense\Retrieve\RetrieveExpenseAction;
use Src\Domain\ValueObjects\Id;
use Src\Interfaces\Http\Api\V1\Expense\Requests\Register\RegisterExpenseApiRequest;
use Src\Interfaces\Http\Api\V1\Expense\Resources\ExpenseResource;
use Src\Interfaces\Http\Controller;
use Symfony\Component\HttpFoundation\Response;

class ExpenseApiController extends Controller
{
    public function __construct(
        private readonly RegisterExpenseAction $registerExpenseAction,
        private readonly RetrieveExpenseAction $retrieveExpenseAction
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    #[Post(
        path: '/api/v1/expense',
        description: "Create a new expense and receive the expense's data upon successful creation.",
        summary: 'Create a new expense for authenticated user',
        security: ['bearerAuth'],
        requestBody: new RequestBody(
            required: true,
            content: new JsonContent(
                ref: '#/components/schemas/RegisterExpenseSwaggerRequest'
            )
        ),
        tags: ['Expense'],
        responses: [
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_CREATED,
                description: 'Expense registered successfully.',
                content: new JsonContent(ref: '#/components/schemas/ExpenseSwaggerResponse')
            ),
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'The server could not process the request due to invalid input.'
            ),
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Unauthorized'
            ),
        ]
    )]
    /**
     * @throws Exception
     */
    public function store(RegisterExpenseApiRequest $request): JsonResponse
    {
        $dto = RegisterExpenseDTO::fromRequest($request);

        $expense = $this->registerExpenseAction->handle($dto);

        return response()->json(
            data: ['data' => ExpenseResource::make($expense)],
            status: Response::HTTP_CREATED,
            options: JSON_PRETTY_PRINT
        );
    }

    #[Get(
        path: '/api/v1/expense',
        description: 'Retrieves the detailed expense record for the authenticated user but only expenses that the authenticated user has permission to view will be returned.',
        summary: 'Retrieves the detailed expense record for the authenticated user.',
        security: ['bearerAuth'],
        tags: ['Expense'],
        parameters: [
            new Parameter(
                name: 'id',
                description: 'The id (uuid) of the expense record to be retrieved.',
                in: 'path',
                required: true,
                schema: new Schema(
                    type: 'string'
                ),
                example: '31e7d216-58d3-4fdd-8a87-57c16adbbf63'
            ),
        ],
        responses: [
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_OK,
                description: 'Expense registered successfully.',
                content: new JsonContent(ref: '#/components/schemas/ExpenseSwaggerResponse')
            ),
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'The server could not process the request due to invalid input.'
            ),
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function show(string $id): ExpenseResource
    {
        $id = new Id($id);

        $expense = $this->retrieveExpenseAction->handle($id);

        Gate::authorize('view', $expense);

        return ExpenseResource::make($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
