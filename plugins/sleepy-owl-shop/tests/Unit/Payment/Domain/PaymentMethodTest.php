<?php

declare(strict_types=1);

use SleepyOwl\Payment\Domain\Model\ValueObject\PaymentMethod;

test('has stripe connect case', function () {
    expect(PaymentMethod::StripeConnect->value)->toBe('stripe_connect');
});

test('has liqpay case', function () {
    expect(PaymentMethod::LiqPay->value)->toBe('liqpay');
});

test('from backs correct case', function () {
    expect(PaymentMethod::from('liqpay'))->toBe(PaymentMethod::LiqPay);
});

test('tryFrom returns null for unknown value', function () {
    expect(PaymentMethod::tryFrom('paypal'))->toBeNull();
});