<?php

declare(strict_types=1);

use SleepyOwl\Shipping\Domain\Exception\ShippingException;
use SleepyOwl\Shipping\Domain\Model\ValueObject\TrackingNumber;

test('creates tracking number with valid value', function () {
    $tn = new TrackingNumber('59000123456789');

    expect($tn->getValue())->toBe('59000123456789');
});

test('rejects empty value', function () {
    expect(fn () => new TrackingNumber(''))->toThrow(ShippingException::class);
});

test('equals returns true for same value', function () {
    $a = new TrackingNumber('59000111111111');
    $b = new TrackingNumber('59000111111111');

    expect($a->equals($b))->toBeTrue();
});

test('equals returns false for different value', function () {
    $a = new TrackingNumber('59000111111111');
    $b = new TrackingNumber('59000222222222');

    expect($a->equals($b))->toBeFalse();
});

test('toString returns value', function () {
    $tn = new TrackingNumber('59000123456789');

    expect((string) $tn)->toBe('59000123456789');
});