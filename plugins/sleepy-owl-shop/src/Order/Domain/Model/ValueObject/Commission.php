<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Model\ValueObject;

use SleepyOwl\Order\Domain\Exception\CommissionException;
use SleepyOwl\Shared\Domain\Money;

final readonly class Commission
{
    public function __construct(private int $rate)
    {
        if ($rate < 0 || $rate > 100) {
            throw new CommissionException(
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
            throw new CommissionException('Cannot apply zero commission rate.');
        }

        $amount = (int) round($subtotal->getAmount() * $this->rate / 100);

        if ($amount === 0) {
            throw new CommissionException('Commission amount rounds to zero.');
        }

        return new Money($amount, $subtotal->getCurrency());
    }

    public function equals(self $other): bool
    {
        return $this->rate === $other->rate;
    }
}
