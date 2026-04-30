<?php

declare(strict_types=1);

namespace SleepyOwl\Review\Domain\Model\ValueObject;

enum ReviewStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}