<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Model\ValueObject;

enum SubOrderStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case Dispatched = 'dispatched';
    case Completed  = 'completed';
}
