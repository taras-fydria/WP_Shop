<?php

declare(strict_types=1);

namespace SleepyOwl\Shipping\Domain\Model\Aggregate;

use DateTimeImmutable;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Shared\Domain\AggregateRoot;
use SleepyOwl\Shared\Domain\Model\ValueObject\DeliveryAddress;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Shipping\Domain\Event\ShipmentCreated;
use SleepyOwl\Shipping\Domain\Event\ShipmentDelivered;
use SleepyOwl\Shipping\Domain\Event\ShipmentDispatched;
use SleepyOwl\Shipping\Domain\Event\TrackingUpdated;
use SleepyOwl\Shipping\Domain\Exception\ShippingException;
use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentId;
use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentStatus;
use SleepyOwl\Shipping\Domain\Model\ValueObject\TrackingNumber;

final class Shipment extends AggregateRoot
{
    private ShipmentStatus $status;
    private ?TrackingNumber $tracking = null;

    private function __construct(
        private readonly ShipmentId      $id,
        private readonly VendorId        $vendorId,
        private readonly SubOrderId      $subOrderId,
        private readonly DeliveryAddress $address,
        private readonly string          $provider,
        private readonly DateTimeImmutable $createdAt,
    ) {
        $this->status = ShipmentStatus::Created;
    }

    public static function create(
        ShipmentId      $id,
        VendorId        $vendorId,
        SubOrderId      $subOrderId,
        DeliveryAddress $address,
        string          $provider,
    ): self {
        $shipment = new self($id, $vendorId, $subOrderId, $address, $provider, new DateTimeImmutable());
        $shipment->raiseEvent(new ShipmentCreated($id));

        return $shipment;
    }

    public function dispatch(TrackingNumber $tracking): void
    {
        if ($this->status !== ShipmentStatus::Created) {
            throw new ShippingException('Shipment can only be dispatched from created status.');
        }

        $this->status   = ShipmentStatus::Dispatched;
        $this->tracking = $tracking;
        $this->raiseEvent(new ShipmentDispatched($this->id, $tracking));
    }

    public function updateStatus(ShipmentStatus $status): void
    {
        if ($this->status === ShipmentStatus::Delivered) {
            throw new ShippingException('Cannot update status of a delivered shipment.');
        }

        $this->status = $status;
        $this->raiseEvent(new TrackingUpdated($this->id, $status));
    }

    public function markDelivered(): void
    {
        if ($this->status === ShipmentStatus::Created) {
            throw new ShippingException('Cannot mark as delivered before dispatching.');
        }

        $this->status = ShipmentStatus::Delivered;
        $this->raiseEvent(new ShipmentDelivered($this->id));
    }

    public function getId(): ShipmentId
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

    public function getAddress(): DeliveryAddress
    {
        return $this->address;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getStatus(): ShipmentStatus
    {
        return $this->status;
    }

    public function getTracking(): ?TrackingNumber
    {
        return $this->tracking;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}