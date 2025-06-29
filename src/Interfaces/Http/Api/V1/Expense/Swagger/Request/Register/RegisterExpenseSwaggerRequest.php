<?php

namespace Src\Interfaces\Http\Api\V1\Expense\Swagger\Request\Register;

use OpenApi\Attributes as OA;

/**
 * @codeCoverageIgnore
 */
#[OA\Schema(
    title: 'Register expense request for authenticated user',
    description: 'Base request for expense register operation for authenticated user.',
    required: ['amount', 'description'],
    type: 'object',
    example: [
        'amount' => '12',
        'description' => 'Expense description example',
    ]
)]
class RegisterExpenseSwaggerRequest
{
    #[OA\Property(
        property: 'amount',
        description: 'The amount of the expense',
        type: 'string',
        minimum: 1,
        example: '12'
    )]
    public string $amount;

    #[OA\Property(
        property: 'description',
        description: 'The description of the expense.',
        type: 'string',
        minLength: 3,
        example: 'Expense description example',
    )]
    public string $description;
}
