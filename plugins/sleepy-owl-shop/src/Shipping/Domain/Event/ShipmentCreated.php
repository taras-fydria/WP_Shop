<?php

declare(strict_types=1);

namespace SleepyOwl\Shipping\Domain\Event;

use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;
use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentId;

final readonly class ShipmentCreated extends AbstractDomainEvent
{
    public function __construct(public ShipmentId $shipmentId)
    {
        parent::__construct();
    }
}