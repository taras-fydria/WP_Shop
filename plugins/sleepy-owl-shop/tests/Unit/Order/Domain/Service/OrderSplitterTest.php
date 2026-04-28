<?php

declare(strict_types=1);

use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Order\Domain\Model\Entity\VendorSubOrder;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderLine;
use SleepyOwl\Order\Domain\Service\CommissionEngine;
use SleepyOwl\Order\Domain\Service\OrderSplitter;
use SleepyOwl\Shared\Domain\Money;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorId;

function makeSplitter(int $defaultRate = 10): OrderSplitter
{
    return new OrderSplitter(new CommissionEngine($defaultRate));
}

test('creates one sub-order per vendor', function () {
    $lines = [
        new OrderLine(new ProductId('p1'), new VendorId('vendor-a'), 1, new Money(100, 'UAH')),
        new OrderLine(new ProductId('p2'), new VendorId('vendor-b'), 1, new Money(200, 'UAH')),
    ];

    $subOrders = makeSplitter()->split($lines);

    expect($subOrders)->toHaveCount(2);
});

test('groups lines by vendor into same sub-order', function () {
    $lines = [
        new OrderLine(new ProductId('p1'), new VendorId('vendor-a'), 1, new Money(100, 'UAH')),
        new OrderLine(new ProductId('p2'), new VendorId('vendor-a'), 2, new Money(50, 'UAH')),
        new OrderLine(new ProductId('p3'), new VendorId('vendor-b'), 1, new Money(300, 'UAH')),
    ];

    $subOrders = makeSplitter()->split($lines);

    expect($subOrders)->toHaveCount(2);

    $vendorAOrder = array_values(array_filter(
        $subOrders,
        fn (VendorSubOrder $s) => $s->getVendorId()->getValue() === 'vendor-a',
    ))[0];

    expect($vendorAOrder->getLines())->toHaveCount(2);
});

test('sub-order subtotal equals sum of its lines', function () {
    $lines = [
        new OrderLine(new ProductId('p1'), new VendorId('vendor-a'), 2, new Money(100, 'UAH')),
        new OrderLine(new ProductId('p2'), new VendorId('vendor-a'), 1, new Money(50, 'UAH')),
    ];

    $subOrders = makeSplitter()->split($lines);

    expect($subOrders[0]->getSubtotal()->getAmount())->toBe(250);
});

test('single vendor produces single sub-order', function () {
    $lines = [
        new OrderLine(new ProductId('p1'), new VendorId('v'), 3, new Money(100, 'UAH')),
    ];

    $subOrders = makeSplitter()->split($lines);

    expect($subOrders)->toHaveCount(1)
        ->and($subOrders[0])->toBeInstanceOf(VendorSubOrder::class);
});

test('applies commission rate from engine to each sub-order', function () {
    $lines = [
        new OrderLine(new ProductId('p1'), new VendorId('v'), 1, new Money(200, 'UAH')),
    ];
    $engine   = new CommissionEngine(defaultRate: 15);
    $splitter = new OrderSplitter($engine);

    $subOrders = $splitter->split($lines);

    expect($subOrders[0]->getCommission()->getRate())->toBe(15);
});

test('returns VendorSubOrder instances', function () {
    $lines = [
        new OrderLine(new ProductId('p'), new VendorId('v'), 1, new Money(100, 'UAH')),
    ];

    $subOrders = makeSplitter()->split($lines);

    expect($subOrders[0])->toBeInstanceOf(VendorSubOrder::class);
});
