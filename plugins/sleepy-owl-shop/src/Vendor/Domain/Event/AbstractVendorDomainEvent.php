<?php

namespace SleepyOwl\Vendor\Domain\Event;

use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

abstract readonly  class AbstractVendorDomainEvent extends AbstractDomainEvent
{
    public function __construct(
        public VendorId $vendorId,
    )
    {
        parent::__construct();
    }
}