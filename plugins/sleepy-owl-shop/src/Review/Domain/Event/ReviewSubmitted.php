<?php

declare(strict_types=1);

namespace SleepyOwl\Review\Domain\Event;

use SleepyOwl\Review\Domain\Model\ValueObject\ReviewId;
use SleepyOwl\Shared\Domain\Events\AbstractDomainEvent;

final readonly class ReviewSubmitted extends AbstractDomainEvent
{
    public function __construct(public ReviewId $reviewId)
    {
        parent::__construct();
    }
}