<?php

declare(strict_types=1);

use SleepyOwl\Review\Domain\Model\ValueObject\ReviewStatus;

test('has pending case', function () {
    expect(ReviewStatus::Pending->value)->toBe('pending');
});

test('has approved case', function () {
    expect(ReviewStatus::Approved->value)->toBe('approved');
});

test('has rejected case', function () {
    expect(ReviewStatus::Rejected->value)->toBe('rejected');
});

test('from backs correct case', function () {
    expect(ReviewStatus::from('approved'))->toBe(ReviewStatus::Approved);
});

test('tryFrom returns null for unknown value', function () {
    expect(ReviewStatus::tryFrom('flagged'))->toBeNull();
});