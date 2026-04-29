<?php

declare(strict_types=1);


use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;

test('creates commission rate with valid rate', function () {
    $rate = new CommissionRate(10);

    expect($rate->getRate())->toBe(10);
});

test('accepts rate of zero', function () {
    $rate = new CommissionRate(0);

    expect($rate->isZero())->toBeTrue()
        ->and($rate->getRate())->toBe(0);
});

test('accepts rate of 100', function () {
    $rate = new CommissionRate(100);

    expect($rate->getRate())->toBe(100);
});

test('rejects negative rate', function () {
    expect(fn () => new CommissionRate(-1))->toThrow(\DomainException::class);
});

test('rejects rate above 100', function () {
    expect(fn () => new CommissionRate(101))->toThrow(\DomainException::class);
});

test('applies to money and returns platform cut', function () {
    $rate     = new CommissionRate(10);
    $subtotal = new Money(1000, 'UAH');

    $cut = $rate->applyTo($subtotal);

    expect($cut->getAmount())->toBe(100)
        ->and($cut->getCurrency())->toBe('UAH');
});

test('applyTo preserves currency', function () {
    $rate     = new CommissionRate(20);
    $subtotal = new Money(500, 'USD');

    $cut = $rate->applyTo($subtotal);

    expect($cut->getCurrency())->toBe('USD');
});

test('throws when applying zero rate', function () {
    $rate = new CommissionRate(0);

    expect(fn () => $rate->applyTo(new Money(1000, 'UAH')))
        ->toThrow(\DomainException::class);
});

test('is equal to rate with same value', function () {
    $a = new CommissionRate(15);
    $b = new CommissionRate(15);

    expect($a->equals($b))->toBeTrue();
});

test('is not equal to rate with different value', function () {
    $a = new CommissionRate(15);
    $b = new CommissionRate(20);

    expect($a->equals($b))->toBeFalse();
});

test('applyTo returns new Money instance — immutable', function () {
    $rate     = new CommissionRate(10);
    $subtotal = new Money(1000, 'UAH');

    $cut = $rate->applyTo($subtotal);

    expect($cut)->not->toBe($subtotal);
});

test('full rate returns equal amount', function () {
    $rate     = new CommissionRate(100);
    $subtotal = new Money(500, 'UAH');

    $cut = $rate->applyTo($subtotal);

    expect($cut->getAmount())->toBe(500);
});
