<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Model\ValueObject;

use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Order\Domain\Exception\OrderException;
use SleepyOwl\Shared\Domain\Money;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorId;

final class OrderLine
{
    private readonly Money $lineTotal;

    public function __construct(
        private readonly ProductId $productId,
        private readonly VendorId $vendorId,
        private readonly int $quantity,
        private readonly Money $unitPrice,
    ) {
        if ($quantity < 1) {
            throw new OrderException('OrderLine quantity must be at least 1.');
        }

        $this->lineTotal = $unitPrice->multiply($quantity);
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getVendorId(): VendorId
    {
        return $this->vendorId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function getLineTotal(): Money
    {
        return $this->lineTotal;
    }
}
