<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Model\ValueObject;

use SleepyOwl\Cart\Domain\Exception\CartException;

final readonly class Quantity
{
    public function __construct(private int $value)
    {
        if ($value < 1) {
            throw new CartException("Quantity must be at least 1, got {$value}.");
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
