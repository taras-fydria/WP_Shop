<?php

declare(strict_types=1);

namespace SleepyOwl\Review\Domain\Model\ValueObject;

use SleepyOwl\Review\Domain\Exception\ReviewException;

final readonly class Rating
{
    public function __construct(private int $value)
    {
        if ($value < 1 || $value > 5) {
            throw new ReviewException('Rating must be between 1 and 5.');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}