<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Query;

use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;

final readonly class ListVendorsQuery
{
    public function __construct(public ?VendorStatus $status = null) {}
}
