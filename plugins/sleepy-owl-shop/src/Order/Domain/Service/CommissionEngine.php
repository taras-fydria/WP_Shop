<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Service;

use SleepyOwl\Order\Domain\Model\ValueObject\Commission;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorId;

final class CommissionEngine
{
    /**
     * @param array<string, int> $vendorRates  VendorId value => rate override
     */
    public function __construct(
        private readonly int $defaultRate,
        private readonly array $vendorRates = [],
    ) {}

    public function calculateFor(VendorId $vendorId): Commission
    {
        $rate = $this->vendorRates[$vendorId->getValue()] ?? $this->defaultRate;
        return new Commission($rate);
    }
}
