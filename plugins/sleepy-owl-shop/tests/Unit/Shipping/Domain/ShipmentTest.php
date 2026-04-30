<?php

declare(strict_types=1);

use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Shared\Domain\Model\ValueObject\DeliveryAddress;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Shipping\Domain\Event\ShipmentCreated;
use SleepyOwl\Shipping\Domain\Event\ShipmentDelivered;
use SleepyOwl\Shipping\Domain\Event\ShipmentDispatched;
use SleepyOwl\Shipping\Domain\Event\TrackingUpdated;
use SleepyOwl\Shipping\Domain\Exception\ShippingException;
use SleepyOwl\Shipping\Domain\Model\Aggregate\Shipment;
use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentId;
use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentStatus;
use SleepyOwl\Shipping\Domain\Model\ValueObject\TrackingNumber;

function makeShipment(): Shipment
{
    return Shipment::create(
        new ShipmentId('shipment-1'),
        new VendorId('vendor-1'),
        new SubOrderId('suborder-1'),
        new DeliveryAddress('UA', 'Kyiv', 'Khreshchatyk 1', '01001'),
        'nova_poshta',
    );
}

test('create returns shipment in created status', function () {
    expect(makeShipment()->getStatus())->toBe(ShipmentStatus::Created);
});

test('create raises ShipmentCreated event', function () {
    $events = makeShipment()->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ShipmentCreated::class)
        ->and($events[0]->shipmentId->getValue())->toBe('shipment-1');
});

test('create sets null tracking', function () {
    expect(makeShipment()->getTracking())->toBeNull();
});

test('dispatch transitions created to dispatched', function () {
    $shipment = makeShipment();
    $shipment->dispatch(new TrackingNumber('1234567890'));

    expect($shipment->getStatus())->toBe(ShipmentStatus::Dispatched);
});

test('dispatch sets tracking number', function () {
    $shipment = makeShipment();
    $shipment->dispatch(new TrackingNumber('1234567890'));

    expect($shipment->getTracking()->getValue())->toBe('1234567890');
});

test('dispatch raises ShipmentDispatched event', function () {
    $shipment = makeShipment();
    $shipment->releaseEvents();
    $shipment->dispatch(new TrackingNumber('1234567890'));

    $events = $shipment->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ShipmentDispatched::class)
        ->and($events[0]->shipmentId->getValue())->toBe('shipment-1')
        ->and($events[0]->trackingNumber->getValue())->toBe('1234567890');
});

test('cannot dispatch already dispatched shipment', function () {
    $shipment = makeShipment();
    $shipment->dispatch(new TrackingNumber('1234567890'));

    expect(fn () => $shipment->dispatch(new TrackingNumber('0987654321')))->toThrow(ShippingException::class);
});

test('updateStatus raises TrackingUpdated event', function () {
    $shipment = makeShipment();
    $shipment->dispatch(new TrackingNumber('1234567890'));
    $shipment->releaseEvents();
    $shipment->updateStatus(ShipmentStatus::InTransit);

    $events = $shipment->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(TrackingUpdated::class)
        ->and($events[0]->status)->toBe(ShipmentStatus::InTransit);
});

test('cannot updateStatus on delivered shipment', function () {
    $shipment = makeShipment();
    $shipment->dispatch(new TrackingNumber('1234567890'));
    $shipment->markDelivered();

    expect(fn () => $shipment->updateStatus(ShipmentStatus::InTransit))->toThrow(ShippingException::class);
});

test('markDelivered transitions dispatched to delivered', function () {
    $shipment = makeShipment();
    $shipment->dispatch(new TrackingNumber('1234567890'));
    $shipment->markDelivered();

    expect($shipment->getStatus())->toBe(ShipmentStatus::Delivered);
});

test('markDelivered raises ShipmentDelivered event', function () {
    $shipment = makeShipment();
    $shipment->dispatch(new TrackingNumber('1234567890'));
    $shipment->releaseEvents();
    $shipment->markDelivered();

    $events = $shipment->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ShipmentDelivered::class)
        ->and($events[0]->shipmentId->getValue())->toBe('shipment-1');
});

test('cannot markDelivered from created status', function () {
    expect(fn () => makeShipment()->markDelivered())->toThrow(ShippingException::class);
});

test('releaseEvents clears event buffer', function () {
    $shipment = makeShipment();
    $shipment->releaseEvents();

    expect($shipment->releaseEvents())->toBeEmpty();
});