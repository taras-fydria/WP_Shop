<?php

declare(strict_types=1);

namespace SleepyOwl\Catalog\Domain\Event;

use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;

final readonly class ProductDeactivated extends AbstractDomainEvent
{
    public function __construct(public ProductId $productId)
    {
        parent::__construct();
    }
}