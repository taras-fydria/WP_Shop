<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Model\Aggregate;

use DateTimeImmutable;
use SleepyOwl\Order\Domain\Event\OrderCancelled;
use SleepyOwl\Order\Domain\Event\OrderCompleted;
use SleepyOwl\Order\Domain\Event\OrderPaid;
use SleepyOwl\Order\Domain\Event\OrderPlaced;
use SleepyOwl\Order\Domain\Event\OrderSplit;
use SleepyOwl\Order\Domain\Exception\OrderException;
use SleepyOwl\Order\Domain\Model\Entity\VendorSubOrder;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderId;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderLine;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderStatus;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderStatus;
use SleepyOwl\Order\Domain\Service\OrderSplitter;
use SleepyOwl\Shared\Domain\AggregateRoot;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;

final class MarketplaceOrder extends AggregateRoot
{
    private OrderStatus $status;

    /** @var VendorSubOrder[] */
    private array $subOrders = [];

    private bool $isSplit = false;

    private function __construct(
        private readonly OrderId $id,
        /** @var OrderLine[] */
        private readonly array $lines,
        private readonly Money $totalAmount,
        private readonly DateTimeImmutable $placedAt,
    ) {
        $this->status = OrderStatus::Pending;
    }

    /**
     * @param OrderLine[] $lines
     */
    public static function place(OrderId $id, array $lines, Money $totalAmount): self
    {
        if (empty($lines)) {
            throw new OrderException('Cannot place an order with no lines.');
        }

        $order = new self($id, $lines, $totalAmount, new DateTimeImmutable());
        $order->raiseEvent(new OrderPlaced($id));

        return $order;
    }

    public function markAsPaid(): void
    {
        if ($this->status !== OrderStatus::Pending) {
            throw new OrderException(
                "Cannot mark as paid from status: {$this->status->value}.",
            );
        }

        $this->status = OrderStatus::Paid;
        $this->raiseEvent(new OrderPaid($this->id));
    }

    public function split(OrderSplitter $splitter): void
    {
        if ($this->isSplit) {
            throw new OrderException('Order already split.');
        }

        if ($this->status !== OrderStatus::Paid) {
            throw new OrderException(
                "Cannot split order with status: {$this->status->value}.",
            );
        }

        $this->subOrders = $splitter->split($this->lines);
        $this->isSplit   = true;
        $this->status    = OrderStatus::Processing;
        $this->raiseEvent(new OrderSplit($this->id, count($this->subOrders)));
    }

    public function complete(): void
    {
        foreach ($this->subOrders as $subOrder) {
            if ($subOrder->getStatus() !== SubOrderStatus::Completed) {
                throw new OrderException('Cannot complete order: not all sub-orders are completed.');
            }
        }

        $this->status = OrderStatus::Completed;
        $this->raiseEvent(new OrderCompleted($this->id));
    }

    public function cancel(): void
    {
        foreach ($this->subOrders as $subOrder) {
            if ($subOrder->getStatus() === SubOrderStatus::Dispatched) {
                throw new OrderException('Cannot cancel order: a sub-order has already been dispatched.');
            }
        }

        $this->status = OrderStatus::Cancelled;
        $this->raiseEvent(new OrderCancelled($this->id));
    }

    public function getId(): OrderId
    {
        return $this->id;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    /** @return VendorSubOrder[] */
    public function getSubOrders(): array
    {
        return $this->subOrders;
    }

    public function getTotalAmount(): Money
    {
        return $this->totalAmount;
    }

    public function getPlacedAt(): DateTimeImmutable
    {
        return $this->placedAt;
    }
}
