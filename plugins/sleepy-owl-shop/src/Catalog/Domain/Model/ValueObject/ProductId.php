<?php

declare(strict_types=1);

namespace SleepyOwl\Catalog\Domain\Model\ValueObject;

final readonly class ProductId
{
    public function __construct(private string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('ProductId cannot be empty.');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
