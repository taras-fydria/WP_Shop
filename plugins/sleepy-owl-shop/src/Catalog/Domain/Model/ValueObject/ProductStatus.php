<?php

declare(strict_types=1);

namespace SleepyOwl\Catalog\Domain\Model\ValueObject;

enum ProductStatus: string
{
    case Draft       = 'draft';
    case Active      = 'active';
    case Deactivated = 'deactivated';
}