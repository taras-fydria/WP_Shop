<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Event;

use DateTimeImmutable;
use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;
use SleepyOwl\Shared\Domain\Events\DomainEvent;

final readonly class CartCleared implements DomainEvent
{
    public function __construct(
        public CartId $cartId,
        public DateTimeImmutable $occurredAt,
    ) {}
}
