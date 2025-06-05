<?php

declare(strict_types=1);

namespace Src\Domain\User\Entities;

use DateTimeImmutable;
use Src\Domain\User\Role\Enums\Role;
use Src\Domain\User\Role\Exceptions\InvalidRoleTransitionException;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\Status\Interfaces\StatusInterface;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class User
{
    private readonly DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        private Name $name,
        private Email $email,
        private Password $password,
        private readonly ?Id $id = null,
        private Role $role = Role::USER,
        private StatusInterface $status = new AwaitingActivation,
        private ?DateTimeImmutable $emailConfirmedAt = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {

        $now = new DateTimeImmutable;

        $this->createdAt = $createdAt ?? $now;
        $this->updatedAt = $updatedAt ?? $now;
    }

    public function id(): ?Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function emailConfirmedAt(): ?DateTimeImmutable
    {
        return $this->emailConfirmedAt;
    }

    public function changeName(Name $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
        $this->touch();
    }

    public function changePassword(Password $password): void
    {
        $this->password = $password;
        $this->touch();
    }

    public function becomeAdmin(User $admin): void
    {
        if ($admin->role()->isAdmin() === false || $admin->status()->getStatus()->isActive() === false) {
            throw new InvalidRoleTransitionException(from: Role::USER, to: Role::ADMIN, user: $this);
        }

        $this->setRole(Role::ADMIN);
        $this->touch();
    }

    public function becomeUser(): void
    {
        $this->setRole(Role::USER);
        $this->touch();
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

    public function awaitingActivation(): void
    {
        $this->status->awaitingActivation($this);
        $this->touch();
    }

    public function activate(): void
    {
        $this->status->activate($this);
        $this->touch();
    }

    public function suspend(): void
    {
        $this->status->suspend($this);
        $this->touch();
    }

    public function ban(): void
    {
        $this->status->ban($this);
        $this->touch();
    }

    /**
     * @internal Use only within status transition methods
     * // TODO: Consider making this private and using a dedicated status transition service
     */
    public function setStatus(StatusInterface $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function isEmailAlreadyConfirmed(): bool
    {
        return $this->emailConfirmedAt !== null;
    }

    public function markEmailAsConfirmed(): void
    {
        $this->emailConfirmedAt = new \DateTimeImmutable;
        $this->touch();
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable;
    }

    private function setRole(Role $role): void
    {
        $this->role = $role;
    }
}
