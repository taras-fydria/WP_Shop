<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Command;

use SleepyOwl\Shared\Application\EventBusInterface;
use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;
use SleepyOwl\Vendor\Domain\Repository\VendorRepositoryInterface;

final class RegisterVendorHandler
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendors,
        private readonly EventBusInterface         $eventBus,
    )
    {
    }

    public function __invoke(RegisterVendorCommand $command): void
    {
        $id             = new VendorId($command->vendorId);
        $commissionRate = new CommissionRate($command->commissionRate);
        $vendor         = Vendor::register(
            id: $id,
            businessName: $command->businessName,
            commissionRate: $commissionRate,
        );

        $this->vendors->add($vendor);
        $this->eventBus->dispatch($vendor->releaseEvents());
    }
}