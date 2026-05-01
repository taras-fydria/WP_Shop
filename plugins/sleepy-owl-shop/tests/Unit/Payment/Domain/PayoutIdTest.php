<?php

declare(strict_types=1);

use SleepyOwl\Payment\Domain\Exception\PaymentException;
use SleepyOwl\Payment\Domain\Model\ValueObject\PayoutId;

test('creates payout id with valid value', function () {
    $id = new PayoutId('abc-123');

    expect($id->getValue())->toBe('abc-123');
});

test('rejects empty value', function () {
    expect(fn () => new PayoutId(''))->toThrow(PaymentException::class);
});

test('generate returns non-empty uuid-shaped string', function () {
    $id = PayoutId::generate();

    expect($id->getValue())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

test('generate returns unique values', function () {
    $a = PayoutId::generate();
    $b = PayoutId::generate();

    expect($a->getValue())->not->toBe($b->getValue());
});

test('equals returns true for same value', function () {
    $a = new PayoutId('same-id');
    $b = new PayoutId('same-id');

    expect($a->equals($b))->toBeTrue();
});

test('equals returns false for different value', function () {
    $a = new PayoutId('id-one');
    $b = new PayoutId('id-two');

    expect($a->equals($b))->toBeFalse();
});

test('toString returns value', function () {
    $id = new PayoutId('payout-xyz');

    expect((string) $id)->toBe('payout-xyz');
});