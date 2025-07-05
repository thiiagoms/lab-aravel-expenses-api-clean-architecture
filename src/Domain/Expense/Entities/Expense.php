<?php

declare(strict_types=1);

namespace Src\Domain\Expense\Entities;

use DateTimeImmutable;
use Src\Domain\Expense\Status\Interfaces\StatusInterface;
use Src\Domain\Expense\Status\Pending;
use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

class Expense
{
    private readonly DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly User $user,
        private Amount $amount,
        private Description $description,
        private StatusInterface $status = new Pending,
        private readonly ?Id $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $now = new DateTimeImmutable;

        $this->createdAt = $createdAt ?? $now;
        $this->updatedAt = $updatedAt ?? $now;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function id(): ?Id
    {
        return $this->id;
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function status(): StatusInterface
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function changeDescription(Description $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function changeAmount(Amount $amount): void
    {
        $this->amount = $amount;
        $this->touch();
    }

    public function pending(): void
    {
        $this->status->pending($this);
        $this->touch();
    }

    public function approve(User $admin): void
    {
        $this->status->approve($this, $admin);
    }

    public function reject(): void
    {
        $this->status->reject($this);
    }

    public function changeStatus(StatusInterface $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id?->getValue(),
            'user_id' => $this->user->id()->getValue(),
            'amount' => $this->amount->getValue(),
            'description' => $this->description->getValue(),
            'status' => $this->status->getStatus()->value,
            'created_at' => $this->createdAt->format(DATE_ATOM),
            'updated_at' => $this->updatedAt->format(DATE_ATOM),
        ];
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
