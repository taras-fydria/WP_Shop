<?php

declare(strict_types=1);

use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Review\Domain\Event\ReviewApproved;
use SleepyOwl\Review\Domain\Event\ReviewRejected;
use SleepyOwl\Review\Domain\Event\ReviewSubmitted;
use SleepyOwl\Review\Domain\Exception\ReviewException;
use SleepyOwl\Review\Domain\Model\Aggregate\Review;
use SleepyOwl\Review\Domain\Model\ValueObject\Rating;
use SleepyOwl\Review\Domain\Model\ValueObject\ReviewId;
use SleepyOwl\Review\Domain\Model\ValueObject\ReviewStatus;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

function makeReview(): Review
{
    return Review::submit(
        new ReviewId('review-1'),
        new VendorId('vendor-1'),
        new ProductId('product-1'),
        'buyer-42',
        new Rating(4),
        'Great product!',
    );
}

test('submit returns review in pending status', function () {
    expect(makeReview()->getStatus())->toBe(ReviewStatus::Pending);
});

test('submit raises ReviewSubmitted event', function () {
    $events = makeReview()->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ReviewSubmitted::class)
        ->and($events[0]->reviewId->getValue())->toBe('review-1');
});

test('rating must be between 1 and 5', function () {
    expect(fn () => new Rating(0))->toThrow(ReviewException::class)
        ->and(fn () => new Rating(6))->toThrow(ReviewException::class);
});

test('rating accepts boundary values', function () {
    expect(new Rating(1))->toBeInstanceOf(Rating::class)
        ->and(new Rating(5))->toBeInstanceOf(Rating::class);
});

test('comment cannot be empty', function () {
    expect(fn () => Review::submit(
        new ReviewId('review-2'),
        new VendorId('vendor-1'),
        new ProductId('product-1'),
        'buyer-42',
        new Rating(3),
        '',
    ))->toThrow(ReviewException::class);
});

test('approve transitions pending to approved', function () {
    $review = makeReview();
    $review->approve();

    expect($review->getStatus())->toBe(ReviewStatus::Approved);
});

test('approve raises ReviewApproved event', function () {
    $review = makeReview();
    $review->releaseEvents();
    $review->approve();

    $events = $review->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ReviewApproved::class)
        ->and($events[0]->reviewId->getValue())->toBe('review-1');
});

test('cannot approve already approved review', function () {
    $review = makeReview();
    $review->approve();

    expect(fn () => $review->approve())->toThrow(ReviewException::class);
});

test('reject transitions pending to rejected', function () {
    $review = makeReview();
    $review->reject('inappropriate content');

    expect($review->getStatus())->toBe(ReviewStatus::Rejected);
});

test('reject raises ReviewRejected event with reason', function () {
    $review = makeReview();
    $review->releaseEvents();
    $review->reject('inappropriate content');

    $events = $review->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ReviewRejected::class)
        ->and($events[0]->reason)->toBe('inappropriate content');
});

test('cannot approve rejected review', function () {
    $review = makeReview();
    $review->reject('spam');

    expect(fn () => $review->approve())->toThrow(ReviewException::class);
});

test('cannot reject already rejected review', function () {
    $review = makeReview();
    $review->reject('spam');

    expect(fn () => $review->reject('again'))->toThrow(ReviewException::class);
});

test('releaseEvents clears event buffer', function () {
    $review = makeReview();
    $review->releaseEvents();

    expect($review->releaseEvents())->toBeEmpty();
});
