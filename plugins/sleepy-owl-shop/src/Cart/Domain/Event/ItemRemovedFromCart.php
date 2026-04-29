<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Event;

use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;

final readonly class ItemRemovedFromCart extends AbstractDomainEvent
{
    public function __construct(
        public CartId $cartId,
        public ProductId $productId,
    ) {
        parent::__construct();
    }
}