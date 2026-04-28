<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Model\Entity;

use SleepyOwl\Order\Domain\Event\SubOrderCompleted;
use SleepyOwl\Order\Domain\Event\SubOrderConfirmed;
use SleepyOwl\Order\Domain\Event\SubOrderDispatched;
use SleepyOwl\Order\Domain\Exception\OrderException;
use SleepyOwl\Order\Domain\Model\ValueObject\Commission;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderLine;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderStatus;
use SleepyOwl\Order\Domain\Model\ValueObject\TrackingNumber;
use SleepyOwl\Shared\Domain\DomainEvent;
use SleepyOwl\Shared\Domain\Money;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorId;

final class VendorSubOrder
{
    private SubOrderStatus $status = SubOrderStatus::Pending;
    private readonly Money $subtotal;
    /** @var DomainEvent[] */
    private array $events = [];

    /**
     * @param OrderLine[] $lines
     */
    public function __construct(
        private readonly SubOrderId $id,
        private readonly VendorId $vendorId,
        private readonly array $lines,
        private readonly Commission $commission,
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

        $this->status   = SubOrderStatus::Confirmed;
        $this->events[] = new SubOrderConfirmed($this->id, new \DateTimeImmutable());
    }

    public function dispatch(TrackingNumber $trackingNumber): void
    {
        if ($this->status !== SubOrderStatus::Confirmed) {
            throw new OrderException(
                "Cannot dispatch sub-order with status: {$this->status->value}.",
            );
        }

        $this->status   = SubOrderStatus::Dispatched;
        $this->events[] = new SubOrderDispatched($this->id, $trackingNumber, new \DateTimeImmutable());
    }

    public function complete(): void
    {
        if ($this->status !== SubOrderStatus::Dispatched) {
            throw new OrderException(
                "Cannot complete sub-order with status: {$this->status->value}.",
            );
        }

        $this->status   = SubOrderStatus::Completed;
        $this->events[] = new SubOrderCompleted($this->id, new \DateTimeImmutable());
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

    public function getCommission(): Commission
    {
        return $this->commission;
    }

    /** @return DomainEvent[] */
    public function releaseEvents(): array
    {
        $events       = $this->events;
        $this->events = [];
        return $events;
    }
}
