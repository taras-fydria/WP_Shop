<?php

declare(strict_types=1);

namespace SleepyOwl\Catalog\Domain\Model\ValueObject;

use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final readonly class ProductOwnership
{
    public function __construct(private VendorId $vendorId) {}

    public function getVendorId(): VendorId
    {
        return $this->vendorId;
    }

    public function equals(self $other): bool
    {
        return $this->vendorId->equals($other->vendorId);
    }
}