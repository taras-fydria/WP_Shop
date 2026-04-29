<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Event;

use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;

final readonly class SubOrderCompleted extends AbstractDomainEvent
{
    public function __construct(public SubOrderId $subOrderId)
    {
        parent::__construct();
    }
}