<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Event;

use SleepyOwl\Cart\Domain\Model\Entity\CartItem;
use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;

final readonly class CartCheckedOut extends AbstractDomainEvent
{
    /**
     * @param CartItem[] $items snapshot of cart contents at checkout time
     */
    public function __construct(
        public CartId $cartId,
        public string $buyerRef,
        public array $items,
    ) {
        parent::__construct();
    }
}