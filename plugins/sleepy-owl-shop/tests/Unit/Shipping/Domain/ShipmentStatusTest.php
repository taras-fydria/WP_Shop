<?php

declare(strict_types=1);

use SleepyOwl\Shipping\Domain\Model\ValueObject\ShipmentStatus;

test('has created case', function () {
    expect(ShipmentStatus::Created->value)->toBe('created');
});

test('has dispatched case', function () {
    expect(ShipmentStatus::Dispatched->value)->toBe('dispatched');
});

test('has in transit case', function () {
    expect(ShipmentStatus::InTransit->value)->toBe('in_transit');
});

test('has delivered case', function () {
    expect(ShipmentStatus::Delivered->value)->toBe('delivered');
});

test('from backs correct case', function () {
    expect(ShipmentStatus::from('in_transit'))->toBe(ShipmentStatus::InTransit);
});

test('tryFrom returns null for unknown value', function () {
    expect(ShipmentStatus::tryFrom('lost'))->toBeNull();
});