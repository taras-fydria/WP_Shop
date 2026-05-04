<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Query;

use SleepyOwl\Vendor\Application\DTO\VendorReadModel;
use SleepyOwl\Vendor\Domain\Repository\VendorRepositoryInterface;

final class ListVendorsHandler
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendors,
    ) {}

    /** @return VendorReadModel[] */
    public function __invoke(ListVendorsQuery $query): array
    {
        return array_map(
            VendorReadModel::fromAggregate(...),
            $this->vendors->findAll($query->status),
        );
    }
}