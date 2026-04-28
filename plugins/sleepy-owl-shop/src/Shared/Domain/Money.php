<?php

declare(strict_types=1);

namespace SleepyOwl\Shared\Domain;

use SleepyOwl\Shared\Domain\Errors\MoneyAmountException;
use SleepyOwl\Shared\Domain\Errors\MoneyCurrencyException;

final class Money
{
    public function __construct(
        private readonly int    $amount,
        private readonly string $currency,
    )
    {
        if ($amount <= 0) {
            throw new MoneyAmountException('Money amount must be positive.');
        }

        if ($currency === '') {
            throw new MoneyCurrencyException('Currency must not be empty.');
        }
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        $result = $this->amount - $other->amount;

        if ($result <= 0) {
            throw new MoneyAmountException('Subtraction result must be positive.');
        }

        return new self($result, $this->currency);
    }

    public function multiply(int $factor): self
    {
        if ($factor <= 0) {
            throw new MoneyAmountException('Multiply factor must be positive.');
        }

        return new self($this->amount * $factor, $this->currency);
    }

    public function equals(self $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount === $other->amount;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new MoneyCurrencyException(
                "Currency mismatch: {$this->currency} vs {$other->currency}."
            );
        }
    }
}