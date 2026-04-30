<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Domain\Repository;

use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;

interface VendorRepositoryInterface
{
    public function findById(VendorId $id): ?Vendor;

    public function add(Vendor $vendor): void;

    public function update(Vendor $vendor): void;
}