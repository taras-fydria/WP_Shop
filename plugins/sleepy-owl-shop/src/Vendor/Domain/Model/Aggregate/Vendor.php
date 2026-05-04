<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Domain\Model\Aggregate;

use DateTimeImmutable;
use SleepyOwl\Shared\Domain\AggregateRoot;
use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Event\CommissionRateUpdated;
use SleepyOwl\Vendor\Domain\Event\VendorApproved;
use SleepyOwl\Vendor\Domain\Event\VendorRegistered;
use SleepyOwl\Vendor\Domain\Event\VendorReinstated;
use SleepyOwl\Vendor\Domain\Event\VendorSuspended;
use SleepyOwl\Vendor\Domain\Exception\VendorException;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;

final class Vendor extends AggregateRoot
{
    private VendorStatus $status;
    private CommissionRate $commissionRate;

    private function __construct(
        private readonly VendorId          $id,
        private readonly string            $businessName,
        private readonly DateTimeImmutable $createdAt,
        CommissionRate                     $commissionRate,
    )
    {
        $this->status         = VendorStatus::Pending;
        $this->commissionRate = $commissionRate;
    }

    public static function reconstitute(
        VendorId          $id,
        string            $businessName,
        VendorStatus      $status,
        CommissionRate    $commissionRate,
        DateTimeImmutable $createdAt,
    ): self {
        $vendor         = new self($id, $businessName, $createdAt, $commissionRate);
        $vendor->status = $status;

        return $vendor;
    }

    public static function register(VendorId $id, string $businessName, CommissionRate $commissionRate): self
    {
        $createdAt = new DateTimeImmutable();
        $vendor    = new self(
            id: $id,
            businessName: $businessName,
            createdAt: $createdAt,
            commissionRate: $commissionRate);
        $vendor->raiseEvent(new VendorRegistered($id));

        return $vendor;
    }

    public function approve(): void
    {
        if ($this->status === VendorStatus::Approved) {
            throw new VendorException('Vendor is already approved.');
        }

        $this->status = VendorStatus::Approved;
        $this->raiseEvent(new VendorApproved($this->id));
    }

    public function suspend(string $reason): void
    {
        if ($this->status === VendorStatus::Suspended) {
            throw new VendorException('Vendor is already suspended.');
        }

        $this->status = VendorStatus::Suspended;
        $this->raiseEvent(new VendorSuspended($this->id, $reason));
    }

    public function reinstate(): void
    {
        if ($this->status !== VendorStatus::Suspended) {
            throw new VendorException('Cannot reinstate a vendor that is not suspended.');
        }

        $this->status = VendorStatus::Approved;
        $this->raiseEvent(new VendorReinstated($this->id));
    }

    public function updateCommissionRate(CommissionRate $rate): void
    {
        $this->commissionRate = $rate;
        $this->raiseEvent(new CommissionRateUpdated($this->id, $rate));
    }

    public function getId(): VendorId
    {
        return $this->id;
    }

    public function getStatus(): VendorStatus
    {
        return $this->status;
    }

    public function getCommissionRate(): CommissionRate
    {
        return $this->commissionRate;
    }

    public function getBusinessName(): string
    {
        return $this->businessName;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}