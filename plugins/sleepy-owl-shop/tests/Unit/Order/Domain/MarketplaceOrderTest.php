<?php

declare(strict_types=1);

use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Order\Domain\Event\OrderCancelled;
use SleepyOwl\Order\Domain\Event\OrderCompleted;
use SleepyOwl\Order\Domain\Event\OrderPaid;
use SleepyOwl\Order\Domain\Event\OrderPlaced;
use SleepyOwl\Order\Domain\Event\OrderSplit;
use SleepyOwl\Order\Domain\Exception\OrderException;
use SleepyOwl\Order\Domain\Model\Aggregate\MarketplaceOrder;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderId;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderLine;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderStatus;
use SleepyOwl\Order\Domain\Service\CommissionEngine;
use SleepyOwl\Order\Domain\Service\OrderSplitter;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

function makeLines(): array
{
    return [
        new OrderLine(new ProductId('p1'), new VendorId('vendor-a'), 2, new Money(500, 'UAH')),
        new OrderLine(new ProductId('p2'), new VendorId('vendor-b'), 1, new Money(300, 'UAH')),
    ];
}

function makeSplitterForOrder(): OrderSplitter
{
    return new OrderSplitter(new CommissionEngine(defaultRate: 10));
}

test('place creates order in pending status', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));

    expect($order->getStatus())->toBe(OrderStatus::Pending);
});

test('place raises OrderPlaced event', function () {
    $order  = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $events = $order->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(OrderPlaced::class);
});

test('place rejects empty lines', function () {
    expect(fn () => MarketplaceOrder::place(new OrderId('o'), [], new Money(100, 'UAH')))
        ->toThrow(OrderException::class);
});

test('markAsPaid transitions to paid status', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();

    expect($order->getStatus())->toBe(OrderStatus::Paid);
});

test('markAsPaid raises OrderPaid event', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->releaseEvents();
    $order->markAsPaid();

    $events = $order->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(OrderPaid::class);
});

test('split creates sub-orders and transitions to processing', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();
    $order->split(makeSplitterForOrder());

    expect($order->getStatus())->toBe(OrderStatus::Processing)
        ->and($order->getSubOrders())->toHaveCount(2);
});

test('split raises OrderSplit event with correct sub-order count', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();
    $order->releaseEvents();
    $order->split(makeSplitterForOrder());

    $events = $order->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(OrderSplit::class)
        ->and($events[0]->subOrderCount)->toBe(2);
});

test('cannot split twice', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();
    $order->split(makeSplitterForOrder());

    expect(fn () => $order->split(makeSplitterForOrder()))->toThrow(OrderException::class);
});

test('cannot split unpaid order', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));

    expect(fn () => $order->split(makeSplitterForOrder()))->toThrow(OrderException::class);
});

test('complete transitions to completed when all sub-orders done', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();
    $order->split(makeSplitterForOrder());

    foreach ($order->getSubOrders() as $subOrder) {
        $subOrder->confirm();
        $subOrder->dispatch();
        $subOrder->complete();
    }

    $order->complete();

    expect($order->getStatus())->toBe(OrderStatus::Completed);
});

test('complete raises OrderCompleted event', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();
    $order->split(makeSplitterForOrder());
    $order->releaseEvents();

    foreach ($order->getSubOrders() as $subOrder) {
        $subOrder->confirm();
        $subOrder->dispatch();
        $subOrder->complete();
    }

    $order->complete();
    $events = $order->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(OrderCompleted::class);
});

test('cannot complete when sub-orders are not all done', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();
    $order->split(makeSplitterForOrder());

    expect(fn () => $order->complete())->toThrow(OrderException::class);
});

test('cancel transitions to cancelled when no sub-order dispatched', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();
    $order->split(makeSplitterForOrder());
    $order->cancel();

    expect($order->getStatus())->toBe(OrderStatus::Cancelled);
});

test('cancel raises OrderCancelled event', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();
    $order->split(makeSplitterForOrder());
    $order->releaseEvents();
    $order->cancel();

    $events = $order->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(OrderCancelled::class);
});

test('cannot cancel after a sub-order is dispatched', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->markAsPaid();
    $order->split(makeSplitterForOrder());

    $subOrders = $order->getSubOrders();
    $subOrders[0]->confirm();
    $subOrders[0]->dispatch();

    expect(fn () => $order->cancel())->toThrow(OrderException::class);
});

test('releaseEvents clears event buffer', function () {
    $order = MarketplaceOrder::place(new OrderId('o'), makeLines(), new Money(1300, 'UAH'));
    $order->releaseEvents();

    expect($order->releaseEvents())->toBeEmpty();
});
