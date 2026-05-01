<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Repository;

use SleepyOwl\Order\Domain\Model\Aggregate\MarketplaceOrder;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderId;

interface OrderRepositoryInterface
{
    public function findById(OrderId $id): ?MarketplaceOrder;

    public function add(MarketplaceOrder $order): void;

    public function update(MarketplaceOrder $order): void;

    public function delete(OrderId $id): void;
}