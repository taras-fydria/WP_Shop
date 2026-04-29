<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Event;

use SleepyOwl\Order\Domain\Model\ValueObject\OrderId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;

final readonly class OrderPaid extends AbstractDomainEvent
{
    public function __construct(public OrderId $orderId)
    {
        parent::__construct();
    }
}