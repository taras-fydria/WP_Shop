<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Query;

final readonly class GetVendorQuery
{
    public function __construct(public string $vendorId) {}
}