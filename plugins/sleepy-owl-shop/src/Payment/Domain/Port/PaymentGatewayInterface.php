<?php

declare(strict_types=1);

namespace SleepyOwl\Payment\Domain\Port;

use SleepyOwl\Payment\Domain\Model\Aggregate\Payout;

interface PaymentGatewayInterface
{
    public function initiate(Payout $payout): string;
}