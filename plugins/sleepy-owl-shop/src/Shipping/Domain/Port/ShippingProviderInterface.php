<?php

declare(strict_types=1);

namespace SleepyOwl\Shipping\Domain\Port;

use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentStatus;
use SleepyOwl\Shipping\Domain\Model\ValueObject\TrackingNumber;

interface ShippingProviderInterface
{
    public function getStatus(TrackingNumber $trackingNumber): ShipmentStatus;
}