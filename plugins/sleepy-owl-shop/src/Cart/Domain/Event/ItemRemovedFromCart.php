<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Event;

use DateTimeImmutable;
use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\Events\DomainEvent;

final readonly class ItemRemovedFromCart implements DomainEvent
{
    public function __construct(
        public CartId $cartId,
        public ProductId $productId,
        public DateTimeImmutable $occurredAt,
    ) {}
}
