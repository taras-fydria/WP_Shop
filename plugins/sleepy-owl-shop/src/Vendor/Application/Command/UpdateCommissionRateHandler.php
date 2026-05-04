<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Command;

use SleepyOwl\Shared\Application\EventBusInterface;
use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Exception\VendorNotFoundException;
use SleepyOwl\Vendor\Domain\Repository\VendorRepositoryInterface;

final class UpdateCommissionRateHandler
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendors,
        private readonly EventBusInterface         $eventBus,
    ) {}

    public function __invoke(UpdateCommissionRateCommand $command): void
    {
        $vendorId = new VendorId($command->vendorId);
        $vendor   = $this->vendors->findById($vendorId);

        if ($vendor === null) {
            throw new VendorNotFoundException($vendorId);
        }

        $vendor->updateCommissionRate(new CommissionRate($command->commissionRate));

        $this->vendors->update($vendor);
        $this->eventBus->dispatch($vendor->releaseEvents());
    }
}
