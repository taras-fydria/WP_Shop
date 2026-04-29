<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Event;

use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;

final readonly class CartCleared extends AbstractDomainEvent
{
    public function __construct(public CartId $cartId)
    {
        parent::__construct();
    }
}