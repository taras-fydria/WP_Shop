<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Service;


use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

interface CommissionEngineInterface
{
    public function calculateFor(VendorId $vendorId): CommissionRate;
}
