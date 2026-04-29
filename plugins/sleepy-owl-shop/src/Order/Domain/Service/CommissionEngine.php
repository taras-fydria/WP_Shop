<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Service;


use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final class CommissionEngine implements CommissionEngineInterface
{
    /**
     * @param array<string, int> $vendorRates  VendorId value => rate override
     */
    public function __construct(
        private readonly int $defaultRate,
        private readonly array $vendorRates = [],
    ) {}

    public function calculateFor(VendorId $vendorId): CommissionRate
    {
        $rate = $this->vendorRates[$vendorId->getValue()] ?? $this->defaultRate;
        return new CommissionRate($rate);
    }
}
