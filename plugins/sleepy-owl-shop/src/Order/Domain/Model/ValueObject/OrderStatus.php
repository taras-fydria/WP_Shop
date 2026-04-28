<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Model\ValueObject;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Paid       = 'paid';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
}
