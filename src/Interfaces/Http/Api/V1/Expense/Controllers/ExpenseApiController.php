<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\Expense\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;
use Src\Application\UseCases\Expense\Destroy\DestroyExpenseAction;
use Src\Application\UseCases\Expense\Register\DTO\RegisterExpenseDTO;
use Src\Application\UseCases\Expense\Register\RegisterExpenseAction;
use Src\Application\UseCases\Expense\Update\DTO\UpdateExpenseDTO;
use Src\Application\UseCases\Expense\Update\UpdateExpenseAction;
use Src\Infrastructure\Adapters\Mappers\Expense\ExpenseModelToExpenseEntityMapper;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;
use Src\Interfaces\Http\Api\V1\Expense\Requests\Register\RegisterExpenseApiRequest;
use Src\Interfaces\Http\Api\V1\Expense\Requests\Update\UpdateExpenseApiRequest;
use Src\Interfaces\Http\Api\V1\Expense\Resources\ExpenseResource;
use Src\Interfaces\Http\Controller;
use Symfony\Component\HttpFoundation\Response;

class ExpenseApiController extends Controller
{
    public function __construct(
        private readonly RegisterExpenseAction $registerExpenseAction,
        private readonly UpdateExpenseAction $updateExpenseAction,
        private readonly DestroyExpenseAction $destroyExpenseAction
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
    public function show(LaravelExpenseModel $expense): ExpenseResource
    {
        Gate::authorize('view', $expense);

        $expense = ExpenseModelToExpenseEntityMapper::map($expense);

        return ExpenseResource::make($expense);
    }

    public function update(UpdateExpenseApiRequest $request, LaravelExpenseModel $expense): ExpenseResource
    {
        Gate::authorize('update', $expense);

        $dto = UpdateExpenseDTO::fromRequest(request: $request, id: $expense->id);

        $expense = $this->updateExpenseAction->handle($dto);

        return ExpenseResource::make($expense);
    }

    /**
     * @throws Exception
     */
    public function destroy(LaravelExpenseModel $expense): JsonResponse
    {
        Gate::authorize('update', $expense);

        $this->destroyExpenseAction->handle($expense->id);

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
