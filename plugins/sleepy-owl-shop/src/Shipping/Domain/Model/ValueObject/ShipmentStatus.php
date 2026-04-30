<?php

declare(strict_types=1);

namespace SleepyOwl\Shipping\Domain\Model\ValueObject;

enum ShipmentStatus: string
{
    case Created    = 'created';
    case Dispatched = 'dispatched';
    case InTransit  = 'in_transit';
    case Delivered  = 'delivered';
}