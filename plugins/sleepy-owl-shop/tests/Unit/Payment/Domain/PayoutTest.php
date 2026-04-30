<?php

declare(strict_types=1);

use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Payment\Domain\Event\PayoutCompleted;
use SleepyOwl\Payment\Domain\Event\PayoutFailed;
use SleepyOwl\Payment\Domain\Event\PayoutInitiated;
use SleepyOwl\Payment\Domain\Exception\PaymentException;
use SleepyOwl\Payment\Domain\Model\Aggregate\Payout;
use SleepyOwl\Payment\Domain\Model\ValueObject\PaymentMethod;
use SleepyOwl\Payment\Domain\Model\ValueObject\PayoutId;
use SleepyOwl\Payment\Domain\Model\ValueObject\PayoutStatus;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

function makePayout(): Payout
{
    return Payout::create(
        new PayoutId('payout-1'),
        new VendorId('vendor-1'),
        new SubOrderId('suborder-1'),
        new Money(10000, 'UAH'),
        PaymentMethod::StripeConnect,
    );
}

test('create returns payout in pending status', function () {
    expect(makePayout()->getStatus())->toBe(PayoutStatus::Pending);
});

test('create raises no events', function () {
    expect(makePayout()->releaseEvents())->toBeEmpty();
});

test('initiate transitions pending to initiated', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');

    expect($payout->getStatus())->toBe(PayoutStatus::Initiated);
});

test('initiate sets gatewayRef', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');

    expect($payout->getGatewayRef())->toBe('gw-ref-123');
});

test('initiate raises PayoutInitiated event', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');

    $events = $payout->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(PayoutInitiated::class)
        ->and($events[0]->payoutId->getValue())->toBe('payout-1');
});

test('cannot initiate non-pending payout', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');

    expect(fn () => $payout->initiate('gw-ref-456'))->toThrow(PaymentException::class);
});

test('markCompleted transitions initiated to completed', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');
    $payout->markCompleted();

    expect($payout->getStatus())->toBe(PayoutStatus::Completed);
});

test('markCompleted raises PayoutCompleted event', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');
    $payout->releaseEvents();
    $payout->markCompleted();

    $events = $payout->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(PayoutCompleted::class)
        ->and($events[0]->payoutId->getValue())->toBe('payout-1');
});

test('cannot markCompleted from pending', function () {
    expect(fn () => makePayout()->markCompleted())->toThrow(PaymentException::class);
});

test('markFailed transitions initiated to failed', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');
    $payout->markFailed('insufficient funds');

    expect($payout->getStatus())->toBe(PayoutStatus::Failed);
});

test('markFailed raises PayoutFailed event with reason', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');
    $payout->releaseEvents();
    $payout->markFailed('insufficient funds');

    $events = $payout->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(PayoutFailed::class)
        ->and($events[0]->reason)->toBe('insufficient funds');
});

test('cannot markFailed from completed', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');
    $payout->markCompleted();

    expect(fn () => $payout->markFailed('oops'))->toThrow(PaymentException::class);
});

test('cannot markFailed from pending', function () {
    expect(fn () => makePayout()->markFailed('oops'))->toThrow(PaymentException::class);
});

test('releaseEvents clears event buffer', function () {
    $payout = makePayout();
    $payout->initiate('gw-ref-123');
    $payout->releaseEvents();

    expect($payout->releaseEvents())->toBeEmpty();
});