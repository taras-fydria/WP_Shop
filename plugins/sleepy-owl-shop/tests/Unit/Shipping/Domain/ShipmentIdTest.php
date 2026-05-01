<?php

declare(strict_types=1);

use SleepyOwl\Shipping\Domain\Exception\ShippingException;
use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentId;

test('creates shipment id with valid value', function () {
    $id = new ShipmentId('ship-123');

    expect($id->getValue())->toBe('ship-123');
});

test('rejects empty value', function () {
    expect(fn () => new ShipmentId(''))->toThrow(ShippingException::class);
});

test('generate returns uuid-shaped string', function () {
    $id = ShipmentId::generate();

    expect($id->getValue())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

test('generate returns unique values', function () {
    $a = ShipmentId::generate();
    $b = ShipmentId::generate();

    expect($a->getValue())->not->toBe($b->getValue());
});

test('equals returns true for same value', function () {
    $a = new ShipmentId('same-id');
    $b = new ShipmentId('same-id');

    expect($a->equals($b))->toBeTrue();
});

test('equals returns false for different value', function () {
    $a = new ShipmentId('id-one');
    $b = new ShipmentId('id-two');

    expect($a->equals($b))->toBeFalse();
});

test('toString returns value', function () {
    $id = new ShipmentId('shipment-xyz');

    expect((string) $id)->toBe('shipment-xyz');
});