<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Domain\Event;

use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;
use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final readonly class CommissionRateUpdated extends AbstractDomainEvent
{
    public function __construct(
        public VendorId $vendorId,
        public CommissionRate $newRate,
    ) {
        parent::__construct();
    }
}