<?php

declare(strict_types=1);

namespace SleepyOwl\Shipping\Domain\Event;

use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;
use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentId;
use SleepyOwl\Shipping\Domain\Model\ValueObject\TrackingNumber;

final readonly class ShipmentDispatched extends AbstractDomainEvent
{
    public function __construct(
        public ShipmentId     $shipmentId,
        public TrackingNumber $trackingNumber,
    ) {
        parent::__construct();
    }
}