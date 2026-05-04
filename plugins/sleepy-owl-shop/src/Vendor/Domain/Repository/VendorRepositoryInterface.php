<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Domain\Repository;

use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;

interface VendorRepositoryInterface
{
    public function findById(VendorId $id): ?Vendor;

    /** @return Vendor[] */
    public function findAll(?VendorStatus $status = null): array;

    public function add(Vendor $vendor): void;

    public function update(Vendor $vendor): void;

    public function delete(VendorId $id): void;
}