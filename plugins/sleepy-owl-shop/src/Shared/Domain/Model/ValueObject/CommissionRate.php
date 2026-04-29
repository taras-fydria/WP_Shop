<?php

declare(strict_types=1);

namespace SleepyOwl\Shared\Domain\Model\ValueObject;

final readonly class CommissionRate
{
    public function __construct(private int $rate)
    {
        if ($rate < 0 || $rate > 100) {
            throw new \DomainException(
                "Commission rate must be between 0 and 100, got {$rate}.",
            );
        }
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    public function isZero(): bool
    {
        return $this->rate === 0;
    }

    public function applyTo(Money $subtotal): Money
    {
        if ($this->isZero()) {
            throw new \DomainException('Cannot apply zero commission rate.');
        }

        $amount = (int) round($subtotal->getAmount() * $this->rate / 100);

        if ($amount === 0) {
            throw new \DomainException('Commission amount rounds to zero.');
        }

        return new Money($amount, $subtotal->getCurrency());
    }

    public function equals(self $other): bool
    {
        return $this->rate === $other->rate;
    }
}
