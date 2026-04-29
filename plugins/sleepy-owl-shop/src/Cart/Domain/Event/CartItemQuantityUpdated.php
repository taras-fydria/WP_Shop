<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Event;

use DateTimeImmutable;
use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;
use SleepyOwl\Cart\Domain\Model\ValueObject\Quantity;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\Events\DomainEvent;

final readonly class CartItemQuantityUpdated implements DomainEvent
{
    public function __construct(
        public CartId $cartId,
        public ProductId $productId,
        public Quantity $newQuantity,
        public DateTimeImmutable $occurredAt,
    ) {}
}
