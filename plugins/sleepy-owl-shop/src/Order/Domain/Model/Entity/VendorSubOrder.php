<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Model\Entity;

use DateTimeImmutable;
use SleepyOwl\Order\Domain\Event\SubOrderCompleted;
use SleepyOwl\Order\Domain\Event\SubOrderConfirmed;
use SleepyOwl\Order\Domain\Event\SubOrderDispatched;
use SleepyOwl\Order\Domain\Exception\OrderException;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderLine;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderStatus;
use SleepyOwl\Shared\Domain\AggregateRoot;
use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final class VendorSubOrder extends AggregateRoot
{
    private SubOrderStatus $status = SubOrderStatus::Pending;
    private readonly Money $subtotal;

    /**
     * @param OrderLine[] $lines
     */
    public function __construct(
        private readonly SubOrderId $id,
        private readonly VendorId $vendorId,
        private readonly array $lines,
        private readonly CommissionRate $commissionRate,
    ) {
        if (empty($lines)) {
            throw new OrderException('VendorSubOrder must have at least one line.');
        }

        $subtotal = $lines[0]->getLineTotal();
        foreach (array_slice($lines, 1) as $line) {
            $subtotal = $subtotal->add($line->getLineTotal());
        }
        $this->subtotal = $subtotal;
    }

    public function confirm(): void
    {
        if ($this->status !== SubOrderStatus::Pending) {
            throw new OrderException(
                "Cannot confirm sub-order with status: {$this->status->value}.",
            );
        }

        $this->status = SubOrderStatus::Confirmed;
        $this->raiseEvent(new SubOrderConfirmed($this->id));
    }

    public function dispatch(): void
    {
        if ($this->status !== SubOrderStatus::Confirmed) {
            throw new OrderException(
                "Cannot dispatch sub-order with status: {$this->status->value}.",
            );
        }

        $this->status = SubOrderStatus::Dispatched;
        $this->raiseEvent(new SubOrderDispatched($this->id));
    }

    public function complete(): void
    {
        if ($this->status !== SubOrderStatus::Dispatched) {
            throw new OrderException(
                "Cannot complete sub-order with status: {$this->status->value}.",
            );
        }

        $this->status = SubOrderStatus::Completed;
        $this->raiseEvent(new SubOrderCompleted($this->id));
    }

    public function getId(): SubOrderId
    {
        return $this->id;
    }

    public function getVendorId(): VendorId
    {
        return $this->vendorId;
    }

    /** @return OrderLine[] */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function getStatus(): SubOrderStatus
    {
        return $this->status;
    }

    public function getSubtotal(): Money
    {
        return $this->subtotal;
    }

    public function getCommissionRate(): CommissionRate
    {
        return $this->commissionRate;
    }
}