<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Domain\Model\ValueObject;

enum VendorStatus: string
{
    case Pending   = 'pending';
    case Approved  = 'approved';
    case Suspended = 'suspended';
}