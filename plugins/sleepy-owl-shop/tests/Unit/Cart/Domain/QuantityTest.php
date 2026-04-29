<?php

declare(strict_types=1);

use SleepyOwl\Cart\Domain\Exception\CartException;
use SleepyOwl\Cart\Domain\Model\ValueObject\Quantity;

test('creates quantity with valid value', function () {
    $qty = new Quantity(3);

    expect($qty->getValue())->toBe(3);
});

test('accepts quantity of one', function () {
    $qty = new Quantity(1);

    expect($qty->getValue())->toBe(1);
});

test('rejects zero quantity', function () {
    expect(fn () => new Quantity(0))->toThrow(CartException::class);
});

test('rejects negative quantity', function () {
    expect(fn () => new Quantity(-1))->toThrow(CartException::class);
});

test('add returns new quantity with summed value', function () {
    $a = new Quantity(2);
    $b = new Quantity(3);

    $result = $a->add($b);

    expect($result->getValue())->toBe(5);
});

test('add returns new instance — immutable', function () {
    $a = new Quantity(2);
    $b = new Quantity(3);

    $result = $a->add($b);

    expect($result)->not->toBe($a)
        ->and($a->getValue())->toBe(2);
});

test('is equal to quantity with same value', function () {
    $a = new Quantity(5);
    $b = new Quantity(5);

    expect($a->equals($b))->toBeTrue();
});

test('is not equal to quantity with different value', function () {
    $a = new Quantity(5);
    $b = new Quantity(6);

    expect($a->equals($b))->toBeFalse();
});
