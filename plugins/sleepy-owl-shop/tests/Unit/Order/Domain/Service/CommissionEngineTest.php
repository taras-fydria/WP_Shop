<?php

declare(strict_types=1);

use SleepyOwl\Order\Domain\Service\CommissionEngine;
use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

test('returns default rate for unknown vendor', function () {
    $engine = new CommissionEngine(defaultRate: 10);

    $commission = $engine->calculateFor(new VendorId('vendor-unknown'));

    expect($commission->getRate())->toBe(10);
});

test('returns vendor-specific rate when configured', function () {
    $engine = new CommissionEngine(
        defaultRate: 10,
        vendorRates: ['vendor-vip' => 5],
    );

    $commission = $engine->calculateFor(new VendorId('vendor-vip'));

    expect($commission->getRate())->toBe(5);
});

test('falls back to default rate for vendor without override', function () {
    $engine = new CommissionEngine(
        defaultRate: 15,
        vendorRates: ['vendor-vip' => 5],
    );

    $commission = $engine->calculateFor(new VendorId('vendor-regular'));

    expect($commission->getRate())->toBe(15);
});

test('returns Commission instance', function () {
    $engine = new CommissionEngine(defaultRate: 10);

    $result = $engine->calculateFor(new VendorId('v'));

    expect($result)->toBeInstanceOf(CommissionRate::class);
});

test('supports zero default rate', function () {
    $engine = new CommissionEngine(defaultRate: 0);

    $commission = $engine->calculateFor(new VendorId('v'));

    expect($commission->isZero())->toBeTrue();
});
