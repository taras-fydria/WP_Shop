<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Model\Entity;

use SleepyOwl\Cart\Domain\Model\ValueObject\Quantity;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final class CartItem
{
    public function __construct(
        private readonly ProductId $productId,
        private readonly VendorId $vendorId,
        private readonly Quantity $quantity,
        private readonly Money $unitPrice,
    ) {}

    public function mergeQuantity(Quantity $extra): self
    {
        return new self(
            $this->productId,
            $this->vendorId,
            $this->quantity->add($extra),
            $this->unitPrice,
        );
    }

    public function withQuantity(Quantity $quantity): self
    {
        return new self(
            $this->productId,
            $this->vendorId,
            $quantity,
            $this->unitPrice,
        );
    }

    public function getLineTotal(): Money
    {
        return $this->unitPrice->multiply($this->quantity->getValue());
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getVendorId(): VendorId
    {
        return $this->vendorId;
    }

    public function getQuantity(): Quantity
    {
        return $this->quantity;
    }

    public function getUnitPrice(): Money
    {
        return $this->unitPrice;
    }
}
