<?php

declare(strict_types=1);

namespace SleepyOwl\Shipping\Domain\Repository;

use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Shipping\Domain\Model\Aggregate\Shipment;
use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentId;

interface ShipmentRepositoryInterface
{
    public function findById(ShipmentId $id): ?Shipment;

    public function findBySubOrderId(SubOrderId $id): ?Shipment;

    public function add(Shipment $shipment): void;

    public function update(Shipment $shipment): void;
}