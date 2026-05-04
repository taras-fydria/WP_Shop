<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\DTO;

use DateTimeImmutable;
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;

final readonly class VendorReadModel
{
    public function __construct(
        public string            $id,
        public string            $businessName,
        public string            $status,
        public int|float         $commissionRate,
        public DateTimeImmutable $createdAt,
    ) {}

    public static function fromAggregate(Vendor $vendor): self
    {
        return new self(
            id: $vendor->getId()->getValue(),
            businessName: $vendor->getBusinessName(),
            status: $vendor->getStatus()->value,
            commissionRate: $vendor->getCommissionRate()->getRate(),
            createdAt: $vendor->getCreatedAt(),
        );
    }
}