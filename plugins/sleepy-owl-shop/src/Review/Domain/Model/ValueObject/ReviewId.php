<?php

declare(strict_types=1);

namespace SleepyOwl\Review\Domain\Model\ValueObject;

use SleepyOwl\Review\Domain\Exception\ReviewException;

final readonly class ReviewId
{
    public function __construct(private string $value)
    {
        if (empty($value)) {
            throw new ReviewException('ReviewId cannot be empty.');
        }
    }

    public static function generate(): self
    {
        return new self(sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        ));
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