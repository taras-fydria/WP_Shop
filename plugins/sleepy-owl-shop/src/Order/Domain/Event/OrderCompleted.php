<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Event;

use SleepyOwl\Order\Domain\Model\ValueObject\OrderId;
use SleepyOwl\Shared\Domain\DomainEvent;

final readonly class OrderCompleted implements DomainEvent
{
    public function __construct(
        public OrderId $orderId,
        public \DateTimeImmutable $occurredAt,
    ) {}
}
