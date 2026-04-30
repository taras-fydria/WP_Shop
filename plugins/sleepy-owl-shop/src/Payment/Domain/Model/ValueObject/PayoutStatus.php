<?php

declare(strict_types=1);

namespace SleepyOwl\Payment\Domain\Model\ValueObject;

enum PayoutStatus: string
{
    case Pending   = 'pending';
    case Initiated = 'initiated';
    case Completed = 'completed';
    case Failed    = 'failed';
}