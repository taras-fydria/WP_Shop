<?php

declare(strict_types=1);

namespace SleepyOwl\Catalog\Domain\Model\Aggregate;

use SleepyOwl\Catalog\Domain\Event\ProductActivated;
use SleepyOwl\Catalog\Domain\Event\ProductCreated;
use SleepyOwl\Catalog\Domain\Event\ProductDeactivated;
use SleepyOwl\Catalog\Domain\Event\ProductOwnershipAssigned;
use SleepyOwl\Catalog\Domain\Event\ProductPriceUpdated;
use SleepyOwl\Catalog\Domain\Exception\CatalogException;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductOwnership;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductStatus;
use SleepyOwl\Shared\Domain\AggregateRoot;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final class Product extends AggregateRoot
{
    private ProductStatus $status;
    private ProductOwnership $ownership;
    private Money $price;

    private function __construct(
        private readonly ProductId $id,
        VendorId $vendorId,
        Money $price,
    ) {
        $this->status    = ProductStatus::Draft;
        $this->ownership = new ProductOwnership($vendorId);
        $this->price     = $price;
    }

    public static function create(ProductId $id, VendorId $vendorId, Money $price): self
    {
        $product = new self($id, $vendorId, $price);
        $product->raiseEvent(new ProductCreated($id, $vendorId));

        return $product;
    }

    public function activate(): void
    {
        if ($this->status === ProductStatus::Active) {
            throw new CatalogException('Product is already active.');
        }

        $this->status = ProductStatus::Active;
        $this->raiseEvent(new ProductActivated($this->id));
    }

    public function deactivate(): void
    {
        if ($this->status !== ProductStatus::Active) {
            throw new CatalogException(
                "Cannot deactivate product with status: {$this->status->value}.",
            );
        }

        $this->status = ProductStatus::Deactivated;
        $this->raiseEvent(new ProductDeactivated($this->id));
    }

    public function updatePrice(Money $price): void
    {
        if ($this->status === ProductStatus::Deactivated) {
            throw new CatalogException('Cannot update price of a deactivated product.');
        }

        $this->price = $price;
        $this->raiseEvent(new ProductPriceUpdated($this->id, $price));
    }

    public function assignToVendor(VendorId $vendorId): void
    {
        if ($this->status === ProductStatus::Deactivated) {
            throw new CatalogException('Cannot reassign a deactivated product.');
        }

        $this->ownership = new ProductOwnership($vendorId);
        $this->raiseEvent(new ProductOwnershipAssigned($this->id, $vendorId));
    }

    public function getId(): ProductId
    {
        return $this->id;
    }

    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    public function getOwnership(): ProductOwnership
    {
        return $this->ownership;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }
}