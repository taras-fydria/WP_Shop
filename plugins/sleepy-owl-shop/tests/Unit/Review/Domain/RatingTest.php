<?php

declare(strict_types=1);

use SleepyOwl\Review\Domain\Exception\ReviewException;
use SleepyOwl\Review\Domain\Model\ValueObject\Rating;

test('accepts minimum rating of 1', function () {
    $r = new Rating(1);

    expect($r->getValue())->toBe(1);
});

test('accepts maximum rating of 5', function () {
    $r = new Rating(5);

    expect($r->getValue())->toBe(5);
});

test('accepts mid-range rating', function () {
    $r = new Rating(3);

    expect($r->getValue())->toBe(3);
});

test('rejects rating of zero', function () {
    expect(fn () => new Rating(0))->toThrow(ReviewException::class);
});

test('rejects rating above 5', function () {
    expect(fn () => new Rating(6))->toThrow(ReviewException::class);
});

test('rejects negative rating', function () {
    expect(fn () => new Rating(-1))->toThrow(ReviewException::class);
});

test('equals returns true for same value', function () {
    $a = new Rating(4);
    $b = new Rating(4);

    expect($a->equals($b))->toBeTrue();
});

test('equals returns false for different value', function () {
    $a = new Rating(3);
    $b = new Rating(5);

    expect($a->equals($b))->toBeFalse();
});