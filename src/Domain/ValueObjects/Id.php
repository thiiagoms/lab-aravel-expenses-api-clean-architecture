<?php

declare(strict_types=1);

namespace Src\Domain\ValueObjects;

final readonly class Id
{
    private string $id;

    public function __construct(string $id)
    {
        $this->validate($id);

        $this->id = $id;
    }

    public function getValue(): string
    {
        return $this->id;
    }

    private function validate(string $id): void
    {
        if (uuid_is_valid($id) === false) {
            throw new \InvalidArgumentException("Invalid id given: '{$id}'");
        }
    }
}
