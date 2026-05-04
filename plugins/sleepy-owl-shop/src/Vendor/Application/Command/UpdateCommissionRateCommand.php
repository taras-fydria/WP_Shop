<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Command;

final readonly class UpdateCommissionRateCommand
{
    public function __construct(
        public string $vendorId,
        public float  $commissionRate,
    ) {}
}
