<?php

namespace Src\Interfaces\Http\Api\V1\Expense\Swagger\Response;

use OpenApi\Attributes as OA;

/**
 * @codeCoverageIgnore
 */
#[OA\Schema(
    title: 'Registered expense response',
    description: 'Registered expense response.',
    type: 'object',
)]
class ExpenseSwaggerResponse
{
    #[OA\Property(
        title: 'Data',
        description: 'The data of the created expense.',
        properties: [
            new OA\Property(
                property: 'id',
                title: 'Id',
                description: 'The unique identifier of the expense.',
                type: 'string',
                example: '31e7d216-58d3-4fdd-8a87-57c16adbbf63'
            ),
            new OA\Property(
                property: 'amount',
                title: 'Amount',
                description: 'The value of the expense.',
                type: 'integer',
                example: '122000000'
            ),
            new OA\Property(
                property: 'description',
                title: 'Description',
                description: 'The description address of the expense.',
                type: 'string',
                example: 'Expense example description'
            ),
            new OA\Property(
                property: 'created_at',
                title: 'Created at',
                description: 'The date and time when the user was created.',
                type: 'string',
                format: 'date-time',
                example: '2025-06-30 12:41:15'
            ),
            new OA\Property(
                property: 'updated_at',
                title: 'Updated at',
                description: 'The date and time when the user was updated.',
                type: 'string',
                format: 'date-time',
                example: '2025-06-30 12:41:15'
            ),
        ],
        type: 'object'
    )]
    private object $data;
}
