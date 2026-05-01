<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Command;

final readonly class ApproveVendorCommand
{
    public function __construct(
        public string $vendorId,
    ) {}
}