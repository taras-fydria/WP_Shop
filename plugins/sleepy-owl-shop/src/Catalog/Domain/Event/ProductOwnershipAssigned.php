<?php

declare(strict_types=1);

namespace SleepyOwl\Catalog\Domain\Event;

use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final readonly class ProductOwnershipAssigned extends AbstractDomainEvent
{
    public function __construct(
        public ProductId $productId,
        public VendorId $newOwner,
    ) {
        parent::__construct();
    }
}