<?php

declare(strict_types=1);

namespace SleepyOwl\Catalog\Domain\Event;

use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;

final readonly class ProductPriceUpdated extends AbstractDomainEvent
{
    public function __construct(
        public ProductId $productId,
        public Money $newPrice,
    ) {
        parent::__construct();
    }
}