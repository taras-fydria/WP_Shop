<?php

declare(strict_types=1);

namespace SleepyOwl\Payment\Domain\Model\Aggregate;

use DateTimeImmutable;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Payment\Domain\Event\PayoutCompleted;
use SleepyOwl\Payment\Domain\Event\PayoutFailed;
use SleepyOwl\Payment\Domain\Event\PayoutInitiated;
use SleepyOwl\Payment\Domain\Exception\PaymentException;
use SleepyOwl\Payment\Domain\Model\ValueObject\PaymentMethod;
use SleepyOwl\Payment\Domain\Model\ValueObject\PayoutId;
use SleepyOwl\Payment\Domain\Model\ValueObject\PayoutStatus;
use SleepyOwl\Shared\Domain\AggregateRoot;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final class Payout extends AggregateRoot
{
    private PayoutStatus $status;
    private ?string $gatewayRef       = null;
    private ?DateTimeImmutable $initiatedAt = null;
    private ?DateTimeImmutable $completedAt = null;

    private function __construct(
        private readonly PayoutId      $id,
        private readonly VendorId      $vendorId,
        private readonly SubOrderId    $subOrderId,
        private readonly Money         $amount,
        private readonly PaymentMethod $method,
    ) {
        $this->status = PayoutStatus::Pending;
    }

    public static function create(
        PayoutId      $id,
        VendorId      $vendorId,
        SubOrderId    $subOrderId,
        Money         $amount,
        PaymentMethod $method,
    ): self {
        return new self($id, $vendorId, $subOrderId, $amount, $method);
    }

    public function initiate(string $gatewayRef): void
    {
        if ($this->status !== PayoutStatus::Pending) {
            throw new PaymentException('Payout can only be initiated from pending status.');
        }

        $this->status      = PayoutStatus::Initiated;
        $this->gatewayRef  = $gatewayRef;
        $this->initiatedAt = new DateTimeImmutable();
        $this->raiseEvent(new PayoutInitiated($this->id));
    }

    public function markCompleted(): void
    {
        if ($this->status !== PayoutStatus::Initiated) {
            throw new PaymentException('Payout can only be completed from initiated status.');
        }

        $this->status      = PayoutStatus::Completed;
        $this->completedAt = new DateTimeImmutable();
        $this->raiseEvent(new PayoutCompleted($this->id));
    }

    public function markFailed(string $reason): void
    {
        if ($this->status !== PayoutStatus::Initiated) {
            throw new PaymentException('Payout can only be failed from initiated status.');
        }

        $this->status = PayoutStatus::Failed;
        $this->raiseEvent(new PayoutFailed($this->id, $reason));
    }

    public function getId(): PayoutId
    {
        return $this->id;
    }

    public function getVendorId(): VendorId
    {
        return $this->vendorId;
    }

    public function getSubOrderId(): SubOrderId
    {
        return $this->subOrderId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getMethod(): PaymentMethod
    {
        return $this->method;
    }

    public function getStatus(): PayoutStatus
    {
        return $this->status;
    }

    public function getGatewayRef(): ?string
    {
        return $this->gatewayRef;
    }

    public function getInitiatedAt(): ?DateTimeImmutable
    {
        return $this->initiatedAt;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }
}