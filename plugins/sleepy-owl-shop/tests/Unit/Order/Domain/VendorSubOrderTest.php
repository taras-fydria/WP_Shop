<?php

declare(strict_types=1);

use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Order\Domain\Event\SubOrderCompleted;
use SleepyOwl\Order\Domain\Event\SubOrderConfirmed;
use SleepyOwl\Order\Domain\Event\SubOrderDispatched;
use SleepyOwl\Order\Domain\Exception\OrderException;
use SleepyOwl\Order\Domain\Model\Entity\VendorSubOrder;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderLine;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderStatus;
use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

function makeSubOrder(array $lines = [], int $commissionRate = 10): VendorSubOrder
{
    if (empty($lines)) {
        $lines = [
            new OrderLine(
                new ProductId('p1'),
                new VendorId('vendor-1'),
                2,
                new Money(500, 'UAH'),
            ),
        ];
    }

    return new VendorSubOrder(
        id:             new SubOrderId('sub-1'),
        vendorId:       new VendorId('vendor-1'),
        lines:          $lines,
        commissionRate: new CommissionRate($commissionRate),
    );
}

test('creates sub-order in pending status', function () {
    expect(makeSubOrder()->getStatus())->toBe(SubOrderStatus::Pending);
});

test('computes subtotal from lines', function () {
    $lines = [
        new OrderLine(new ProductId('p1'), new VendorId('v'), 2, new Money(500, 'UAH')),
        new OrderLine(new ProductId('p2'), new VendorId('v'), 1, new Money(300, 'UAH')),
    ];

    expect(makeSubOrder($lines)->getSubtotal()->getAmount())->toBe(1300);
});

test('rejects empty lines', function () {
    expect(fn () => new VendorSubOrder(
        id:             new SubOrderId('s'),
        vendorId:       new VendorId('v'),
        lines:          [],
        commissionRate: new CommissionRate(10),
    ))->toThrow(OrderException::class);
});

test('confirm transitions to confirmed status', function () {
    $subOrder = makeSubOrder();
    $subOrder->confirm();

    expect($subOrder->getStatus())->toBe(SubOrderStatus::Confirmed);
});

test('confirm raises SubOrderConfirmed event', function () {
    $subOrder = makeSubOrder();
    $subOrder->confirm();

    $events = $subOrder->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(SubOrderConfirmed::class);
});

test('cannot confirm twice', function () {
    $subOrder = makeSubOrder();
    $subOrder->confirm();

    expect(fn () => $subOrder->confirm())->toThrow(OrderException::class);
});

test('dispatch transitions to dispatched status', function () {
    $subOrder = makeSubOrder();
    $subOrder->confirm();
    $subOrder->dispatch();

    expect($subOrder->getStatus())->toBe(SubOrderStatus::Dispatched);
});

test('dispatch raises SubOrderDispatched event', function () {
    $subOrder = makeSubOrder();
    $subOrder->confirm();
    $subOrder->releaseEvents();
    $subOrder->dispatch();

    $events = $subOrder->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(SubOrderDispatched::class);
});

test('cannot dispatch without confirming first', function () {
    expect(fn () => makeSubOrder()->dispatch())->toThrow(OrderException::class);
});

test('complete transitions to completed status', function () {
    $subOrder = makeSubOrder();
    $subOrder->confirm();
    $subOrder->dispatch();
    $subOrder->complete();

    expect($subOrder->getStatus())->toBe(SubOrderStatus::Completed);
});

test('complete raises SubOrderCompleted event', function () {
    $subOrder = makeSubOrder();
    $subOrder->confirm();
    $subOrder->dispatch();
    $subOrder->releaseEvents();
    $subOrder->complete();

    $events = $subOrder->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(SubOrderCompleted::class);
});

test('cannot complete without dispatching first', function () {
    $subOrder = makeSubOrder();
    $subOrder->confirm();

    expect(fn () => $subOrder->complete())->toThrow(OrderException::class);
});

test('exposes commission rate snapshot', function () {
    expect(makeSubOrder(commissionRate: 15)->getCommissionRate()->getRate())->toBe(15);
});

test('releaseEvents clears event buffer', function () {
    $subOrder = makeSubOrder();
    $subOrder->confirm();
    $subOrder->releaseEvents();

    expect($subOrder->releaseEvents())->toBeEmpty();
});
