<?php

declare(strict_types=1);

use SleepyOwl\Review\Domain\Exception\ReviewException;
use SleepyOwl\Review\Domain\Model\ValueObject\ReviewId;

test('creates review id with valid value', function () {
    $id = new ReviewId('review-abc');

    expect($id->getValue())->toBe('review-abc');
});

test('rejects empty value', function () {
    expect(fn () => new ReviewId(''))->toThrow(ReviewException::class);
});

test('generate returns uuid-shaped string', function () {
    $id = ReviewId::generate();

    expect($id->getValue())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

test('generate returns unique values', function () {
    $a = ReviewId::generate();
    $b = ReviewId::generate();

    expect($a->getValue())->not->toBe($b->getValue());
});

test('equals returns true for same value', function () {
    $a = new ReviewId('same-id');
    $b = new ReviewId('same-id');

    expect($a->equals($b))->toBeTrue();
});

test('equals returns false for different value', function () {
    $a = new ReviewId('id-one');
    $b = new ReviewId('id-two');

    expect($a->equals($b))->toBeFalse();
});

test('toString returns value', function () {
    $id = new ReviewId('review-xyz');

    expect((string) $id)->toBe('review-xyz');
});