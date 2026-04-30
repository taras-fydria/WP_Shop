<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Repository;

use SleepyOwl\Cart\Domain\Model\Aggregate\Cart;
use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;

interface CartRepositoryInterface
{
    public function findByBuyerRef(string $buyerRef): ?Cart;

    public function add(Cart $cart): void;

    public function update(Cart $cart): void;

    public function delete(CartId $id): void;
}