<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Register\DTO;

use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Support\Sanitizer;
use Src\Interfaces\Http\Api\V1\Expense\Requests\Register\RegisterExpenseApiRequest;

readonly class RegisterExpenseDTO
{
    public function __construct(
        private Id $userId,
        private Amount $amount,
        private Description $description
    ) {}

    public function userId(): Id
    {
        return $this->userId;
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public static function fromRequest(RegisterExpenseApiRequest $request): self
    {
        $payload = Sanitizer::clean($request->validated());

        $id = $request->user('api')->id;

        return new self(
            userId: $id,
            amount: new Amount($payload['amount']),
            description: new Description($payload['description'])
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->getValue(),
            'amount' => $this->amount->getValue(),
            'description' => $this->description->getValue(),
        ];
    }
}
