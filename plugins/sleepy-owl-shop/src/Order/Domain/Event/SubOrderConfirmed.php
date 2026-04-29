<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Event;

use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Shared\Domain\Events\DomainEvent;

final readonly class SubOrderConfirmed implements DomainEvent
{
    public function __construct(
        public SubOrderId $subOrderId,
        public \DateTimeImmutable $occurredAt,
    ) {}
}
