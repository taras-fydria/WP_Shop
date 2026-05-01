<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Command;

final readonly class RegisterVendorCommand
{
    public function __construct(
        public string $vendorId,
        public string $businessName,
        public int    $commissionRate,
    ) {}
}