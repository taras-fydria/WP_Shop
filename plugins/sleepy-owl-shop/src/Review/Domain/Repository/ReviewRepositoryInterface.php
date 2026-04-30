<?php

declare(strict_types=1);

namespace SleepyOwl\Review\Domain\Repository;

use SleepyOwl\Review\Domain\Model\Aggregate\Review;
use SleepyOwl\Review\Domain\Model\ValueObject\ReviewId;

interface ReviewRepositoryInterface
{
    public function findById(ReviewId $id): ?Review;

    public function add(Review $review): void;

    public function update(Review $review): void;
}