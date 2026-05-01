<?php

declare(strict_types=1);

namespace SleepyOwl\Payment\Domain\Repository;

use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Payment\Domain\Model\Aggregate\Payout;
use SleepyOwl\Payment\Domain\Model\ValueObject\PayoutId;

interface PayoutRepositoryInterface
{
    public function findById(PayoutId $id): ?Payout;

    public function findBySubOrderId(SubOrderId $id): ?Payout;

    public function add(Payout $payout): void;

    public function update(Payout $payout): void;

    public function delete(PayoutId $id): void;
}