<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Domain\Exception;

use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final class VendorNotFoundException extends VendorException
{
    public function __construct(VendorId $id)
    {
        parent::__construct("Vendor '{$id->getValue()}' not found.");
    }
}