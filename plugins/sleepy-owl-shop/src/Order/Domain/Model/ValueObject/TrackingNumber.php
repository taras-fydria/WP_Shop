<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Model\ValueObject;

final readonly class TrackingNumber
{
    public function __construct(private string $value)
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('TrackingNumber cannot be empty.');
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
