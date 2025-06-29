<?php

namespace Src\Interfaces\Http\Api\V1\Expense\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Domain\Expense\Entities\Expense;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Expense $expense */
        $expense = $this->resource;

        return [
            'id' => $expense->id()->getValue(),
            'amount' => $expense->amount()->getValue(),
            'description' => $expense->description()->getValue(),
            'created_at' => $expense->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $expense->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
