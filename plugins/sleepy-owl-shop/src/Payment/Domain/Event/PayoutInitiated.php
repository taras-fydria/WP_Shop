<?php

declare(strict_types=1);

namespace SleepyOwl\Payment\Domain\Event;

use SleepyOwl\Payment\Domain\Model\ValueObject\PayoutId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;

final readonly class PayoutInitiated extends AbstractDomainEvent
{
    public function __construct(public PayoutId $payoutId)
    {
        parent::__construct();
    }
}