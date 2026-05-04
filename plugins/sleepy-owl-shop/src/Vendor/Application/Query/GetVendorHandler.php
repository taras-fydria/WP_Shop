<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Query;

use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Application\DTO\VendorReadModel;
use SleepyOwl\Vendor\Domain\Repository\VendorRepositoryInterface;

final class GetVendorHandler
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendors,
    ) {}

    public function __invoke(GetVendorQuery $query): ?VendorReadModel
    {
        $vendor = $this->vendors->findById(new VendorId($query->vendorId));

        return $vendor ? VendorReadModel::fromAggregate($vendor) : null;
    }
}