<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Application\Command;

use SleepyOwl\Shared\Application\EventBusInterface;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Exception\VendorNotFoundException;
use SleepyOwl\Vendor\Domain\Repository\VendorRepositoryInterface;

final class SuspendVendorHandler
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendors,
        private readonly EventBusInterface         $eventBus,
    ) {}

    public function __invoke(SuspendVendorCommand $command): void
    {
        $vendorId = new VendorId($command->vendorId);
        $vendor   = $this->vendors->findById($vendorId);

        if ($vendor === null) {
            throw new VendorNotFoundException($vendorId);
        }

        $vendor->suspend($command->reason);

        $this->vendors->update($vendor);
        $this->eventBus->dispatch($vendor->releaseEvents());
    }
}