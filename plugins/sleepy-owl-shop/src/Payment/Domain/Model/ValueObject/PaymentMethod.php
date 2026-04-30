<?php

declare(strict_types=1);

namespace SleepyOwl\Payment\Domain\Model\ValueObject;

enum PaymentMethod: string
{
    case StripeConnect = 'stripe_connect';
    case LiqPay        = 'liqpay';
}