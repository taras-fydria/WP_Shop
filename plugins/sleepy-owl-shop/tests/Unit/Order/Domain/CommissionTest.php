<?php

declare(strict_types=1);

use SleepyOwl\Order\Domain\Exception\CommissionException;
use SleepyOwl\Order\Domain\Model\ValueObject\Commission;
use SleepyOwl\Shared\Domain\Money;

test('creates commission with valid rate', function () {
    $commission = new Commission(10);

    expect($commission->getRate())->toBe(10);
});

test('accepts rate of zero', function () {
    $commission = new Commission(0);

    expect($commission->isZero())->toBeTrue()
        ->and($commission->getRate())->toBe(0);
});

test('accepts rate of 100', function () {
    $commission = new Commission(100);

    expect($commission->getRate())->toBe(100);
});

test('rejects negative rate', function () {
    expect(fn () => new Commission(-1))->toThrow(CommissionException::class);
});

test('rejects rate above 100', function () {
    expect(fn () => new Commission(101))->toThrow(CommissionException::class);
});

test('applies to money and returns platform cut', function () {
    $commission = new Commission(10);
    $subtotal   = new Money(1000, 'UAH');

    $cut = $commission->applyTo($subtotal);

    expect($cut->getAmount())->toBe(100)
        ->and($cut->getCurrency())->toBe('UAH');
});

test('applyTo preserves currency', function () {
    $commission = new Commission(20);
    $subtotal   = new Money(500, 'USD');

    $cut = $commission->applyTo($subtotal);

    expect($cut->getCurrency())->toBe('USD');
});

test('throws when applying zero commission', function () {
    $commission = new Commission(0);

    expect(fn () => $commission->applyTo(new Money(1000, 'UAH')))
        ->toThrow(CommissionException::class);
});

test('is equal to commission with same rate', function () {
    $a = new Commission(15);
    $b = new Commission(15);

    expect($a->equals($b))->toBeTrue();
});

test('is not equal to commission with different rate', function () {
    $a = new Commission(15);
    $b = new Commission(20);

    expect($a->equals($b))->toBeFalse();
});

test('applyTo returns new Money instance — immutable', function () {
    $commission = new Commission(10);
    $subtotal   = new Money(1000, 'UAH');

    $cut = $commission->applyTo($subtotal);

    expect($cut)->not->toBe($subtotal);
});

test('full commission rate returns equal amount', function () {
    $commission = new Commission(100);
    $subtotal   = new Money(500, 'UAH');

    $cut = $commission->applyTo($subtotal);

    expect($cut->getAmount())->toBe(500);
});
