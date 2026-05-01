<?php

declare(strict_types=1);

use SleepyOwl\Payment\Domain\Model\ValueObject\PayoutStatus;

test('has pending case', function () {
    expect(PayoutStatus::Pending->value)->toBe('pending');
});

test('has initiated case', function () {
    expect(PayoutStatus::Initiated->value)->toBe('initiated');
});

test('has completed case', function () {
    expect(PayoutStatus::Completed->value)->toBe('completed');
});

test('has failed case', function () {
    expect(PayoutStatus::Failed->value)->toBe('failed');
});

test('from backs correct case', function () {
    expect(PayoutStatus::from('completed'))->toBe(PayoutStatus::Completed);
});

test('tryFrom returns null for unknown value', function () {
    expect(PayoutStatus::tryFrom('unknown'))->toBeNull();
});