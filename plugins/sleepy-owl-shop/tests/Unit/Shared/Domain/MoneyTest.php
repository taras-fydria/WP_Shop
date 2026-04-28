<?php

declare(strict_types=1);

use SleepyOwl\Shared\Domain\Errors\MoneyAmountException;
use SleepyOwl\Shared\Domain\Errors\MoneyCurrencyException;
use SleepyOwl\Shared\Domain\Money;

test('creates money with positive amount', function () {
    $money = new Money(100, 'UAH');

    expect($money->getAmount())->toBe(100)
        ->and($money->getCurrency())->toBe('UAH');
});

test('rejects zero amount', function () {
    expect(fn() => new Money(0, 'UAH'))->toThrow(MoneyAmountException::class);
});

test('rejects negative amount', function () {
    expect(fn() => new Money(-1, 'UAH'))->toThrow(MoneyAmountException::class);
});

test('rejects empty currency', function () {
    expect(fn() => new Money(100, ''))->toThrow(MoneyCurrencyException::class);
});

test('adds two money values of same currency', function () {
    $a = new Money(100, 'UAH');
    $b = new Money(50, 'UAH');

    $result = $a->add($b);

    expect($result->getAmount())->toBe(150)
        ->and($result->getCurrency())->toBe('UAH');
});

test('cannot add money of different currencies', function () {
    $a = new Money(100, 'UAH');
    $b = new Money(50, 'USD');

    expect(fn() => $a->add($b))->toThrow(MoneyCurrencyException::class);
});

test('subtracts two money values of same currency', function () {
    $a = new Money(100, 'UAH');
    $b = new Money(30, 'UAH');

    $result = $a->subtract($b);

    expect($result->getAmount())->toBe(70)
        ->and($result->getCurrency())->toBe('UAH');
});

test('cannot subtract to negative result', function () {
    $a = new Money(30, 'UAH');
    $b = new Money(100, 'UAH');

    expect(fn() => $a->subtract($b))->toThrow(MoneyAmountException::class);
});

test('multiplies by integer factor', function () {
    $money = new Money(50, 'UAH');

    $result = $money->multiply(3);

    expect($result->getAmount())->toBe(150)
        ->and($result->getCurrency())->toBe('UAH');
});

test('is equal to money with same amount and currency', function () {
    $a = new Money(100, 'UAH');
    $b = new Money(100, 'UAH');

    expect($a->equals($b))->toBeTrue();
});

test('is not equal when amount differs', function () {
    $a = new Money(100, 'UAH');
    $b = new Money(200, 'UAH');

    expect($a->equals($b))->toBeFalse();
});

test('is immutable — add returns new instance', function () {
    $a = new Money(100, 'UAH');
    $b = new Money(50, 'UAH');

    $result = $a->add($b);

    expect($result)->not->toBe($a)
        ->and($a->getAmount())->toBe(100);
});

test('is not equal when currency differs', function () {
    $a = new Money(100, 'UAH');
    $b = new Money(100, 'USD');

    expect(fn() => $a->equals($b))->toThrow(MoneyCurrencyException::class);
});