<?php

declare(strict_types=1);

namespace SleepyOwl\Cart\Domain\Model\Aggregate;

use DateTimeImmutable;
use SleepyOwl\Cart\Domain\Event\CartCheckedOut;
use SleepyOwl\Cart\Domain\Event\CartCleared;
use SleepyOwl\Cart\Domain\Event\CartItemQuantityUpdated;
use SleepyOwl\Cart\Domain\Event\ItemAddedToCart;
use SleepyOwl\Cart\Domain\Event\ItemRemovedFromCart;
use SleepyOwl\Cart\Domain\Exception\CartException;
use SleepyOwl\Cart\Domain\Model\Entity\CartItem;
use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;
use SleepyOwl\Cart\Domain\Model\ValueObject\Quantity;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\AggregateRoot;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final class Cart extends AggregateRoot
{
    /** @var array<string, CartItem>  keyed by ProductId value */
    private array $items = [];
    private DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly CartId $id,
        private readonly string $buyerRef,
    ) {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addItem(
        ProductId $productId,
        VendorId $vendorId,
        Money $unitPrice,
        Quantity $quantity,
    ): void {
        $key = $productId->getValue();

        if (isset($this->items[$key])) {
            $this->items[$key] = $this->items[$key]->mergeQuantity($quantity);
        } else {
            $this->items[$key] = new CartItem($productId, $vendorId, $quantity, $unitPrice);
        }

        $this->updatedAt = new DateTimeImmutable();
        $this->raiseEvent(new ItemAddedToCart(
            $this->id,
            $productId,
            $vendorId,
            $this->items[$key]->getQuantity(),
            $unitPrice,
            new DateTimeImmutable(),
        ));
    }

    public function removeItem(ProductId $productId): void
    {
        $key = $productId->getValue();

        if (!isset($this->items[$key])) {
            throw new CartException("Product '{$productId->getValue()}' is not in the cart.");
        }

        unset($this->items[$key]);
        $this->updatedAt = new DateTimeImmutable();
        $this->raiseEvent(new ItemRemovedFromCart($this->id, $productId));
    }

    public function updateQuantity(ProductId $productId, Quantity $quantity): void
    {
        $key = $productId->getValue();

        if (!isset($this->items[$key])) {
            throw new CartException("Product '{$productId->getValue()}' is not in the cart.");
        }

        $this->items[$key] = $this->items[$key]->withQuantity($quantity);
        $this->updatedAt   = new DateTimeImmutable();
        $this->raiseEvent(new CartItemQuantityUpdated($this->id, $productId, $quantity));
    }

    public function clear(): void
    {
        $this->items     = [];
        $this->updatedAt = new DateTimeImmutable();
        $this->raiseEvent(new CartCleared($this->id));
    }

    public function checkout(): void
    {
        if (empty($this->items)) {
            throw new CartException('Cannot checkout an empty cart.');
        }

        $this->raiseEvent(new CartCheckedOut(
            $this->id,
            $this->buyerRef,
            array_values($this->items),
            new DateTimeImmutable(),
        ));
    }

    public function getId(): CartId
    {
        return $this->id;
    }

    public function getBuyerRef(): string
    {
        return $this->buyerRef;
    }

    /** @return CartItem[] */
    public function getItems(): array
    {
        return array_values($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
