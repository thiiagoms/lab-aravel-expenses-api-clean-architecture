<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Update\DTO;

use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\ValueObjects\Id;

readonly class UpdateExpenseDTO
{
    public function __construct(
        private Id $id,
        private Id $userId,
        private ?Amount $amount = null,
        private ?Description $description = null
    ) {}

    public function id(): Id
    {
        return $this->id;
    }

    public function userId(): Id
    {
        return $this->userId;
    }

    public function amount(): ?Amount
    {
        return $this->amount;
    }

    public function description(): ?Description
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->getValue(),
            'amount' => $this->amount?->getValue(),
            'description' => $this->description?->getValue(),
        ];
    }
}
