<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Event;

use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Order\Domain\Model\ValueObject\TrackingNumber;
use SleepyOwl\Shared\Domain\DomainEvent;

final readonly class SubOrderDispatched implements DomainEvent
{
    public function __construct(
        public SubOrderId $subOrderId,
        public TrackingNumber $trackingNumber,
        public \DateTimeImmutable $occurredAt,
    ) {}
}
