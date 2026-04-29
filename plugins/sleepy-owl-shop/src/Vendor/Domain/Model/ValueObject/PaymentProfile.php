<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Domain\Model\ValueObject;

use SleepyOwl\Vendor\Domain\Exception\VendorException;

final readonly class PaymentProfile
{
    private function __construct(
        public string $method,
        public string $accountRef,
    ) {
        if (empty($accountRef)) {
            throw new VendorException('Payment profile account reference cannot be empty.');
        }
    }

    public static function stripe(string $accountId): self
    {
        return new self('stripe_connect', $accountId);
    }

    public static function liqPay(string $credentials): self
    {
        return new self('liqpay', $credentials);
    }

    public function equals(self $other): bool
    {
        return $this->method === $other->method && $this->accountRef === $other->accountRef;
    }
}