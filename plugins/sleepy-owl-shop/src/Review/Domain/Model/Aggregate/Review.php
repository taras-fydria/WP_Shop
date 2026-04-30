<?php

declare(strict_types=1);

namespace SleepyOwl\Review\Domain\Model\Aggregate;

use DateTimeImmutable;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Review\Domain\Event\ReviewApproved;
use SleepyOwl\Review\Domain\Event\ReviewRejected;
use SleepyOwl\Review\Domain\Event\ReviewSubmitted;
use SleepyOwl\Review\Domain\Exception\ReviewException;
use SleepyOwl\Review\Domain\Model\ValueObject\Rating;
use SleepyOwl\Review\Domain\Model\ValueObject\ReviewId;
use SleepyOwl\Review\Domain\Model\ValueObject\ReviewStatus;
use SleepyOwl\Shared\Domain\AggregateRoot;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

final class Review extends AggregateRoot
{
    private ReviewStatus $status;
    private ?DateTimeImmutable $moderatedAt = null;

    private function __construct(
        private readonly ReviewId          $id,
        private readonly VendorId          $vendorId,
        private readonly ProductId         $productId,
        private readonly string            $buyerRef,
        private readonly Rating            $rating,
        private readonly string            $comment,
        private readonly DateTimeImmutable $submittedAt,
    ) {
        $this->status = ReviewStatus::Pending;
    }

    public static function submit(
        ReviewId  $id,
        VendorId  $vendorId,
        ProductId $productId,
        string    $buyerRef,
        Rating    $rating,
        string    $comment,
    ): self {
        if (empty($comment)) {
            throw new ReviewException('Review comment cannot be empty.');
        }

        $review = new self($id, $vendorId, $productId, $buyerRef, $rating, $comment, new DateTimeImmutable());
        $review->raiseEvent(new ReviewSubmitted($id));

        return $review;
    }

    public function approve(): void
    {
        if ($this->status !== ReviewStatus::Pending) {
            throw new ReviewException('Review can only be approved from pending status.');
        }

        $this->status      = ReviewStatus::Approved;
        $this->moderatedAt = new DateTimeImmutable();
        $this->raiseEvent(new ReviewApproved($this->id));
    }

    public function reject(string $reason): void
    {
        if ($this->status !== ReviewStatus::Pending) {
            throw new ReviewException('Review can only be rejected from pending status.');
        }

        $this->status      = ReviewStatus::Rejected;
        $this->moderatedAt = new DateTimeImmutable();
        $this->raiseEvent(new ReviewRejected($this->id, $reason));
    }

    public function getId(): ReviewId
    {
        return $this->id;
    }

    public function getVendorId(): VendorId
    {
        return $this->vendorId;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getBuyerRef(): string
    {
        return $this->buyerRef;
    }

    public function getRating(): Rating
    {
        return $this->rating;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getStatus(): ReviewStatus
    {
        return $this->status;
    }

    public function getSubmittedAt(): DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function getModeratedAt(): ?DateTimeImmutable
    {
        return $this->moderatedAt;
    }
}