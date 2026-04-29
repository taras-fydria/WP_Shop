<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Event;

use DateTimeImmutable;
use SleepyOwl\Cart\Domain\Model\Entity\CartItem;
use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;
use SleepyOwl\Shared\Domain\Events\DomainEvent;

final readonly class CartCheckedOut implements DomainEvent
{
    /**
     * @param CartItem[] $items snapshot of cart contents at checkout time
     */
    public function __construct(
        public CartId $cartId,
        public string $buyerRef,
        public array $items,
        public DateTimeImmutable $occurredAt,
    ) {}
}
