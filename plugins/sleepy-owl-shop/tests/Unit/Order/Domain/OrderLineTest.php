<?php

declare(strict_types=1);

use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Order\Domain\Exception\OrderException;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderLine;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

test('creates order line with valid data', function () {
    $line = new OrderLine(
        productId: new ProductId('prod-abc'),
        vendorId:  new VendorId('vendor-xyz'),
        quantity:  2,
        unitPrice: new Money(500, 'UAH'),
    );

    expect($line->getQuantity())->toBe(2)
        ->and($line->getUnitPrice()->getAmount())->toBe(500)
        ->and($line->getLineTotal()->getAmount())->toBe(1000);
});

test('line total equals unit price when quantity is 1', function () {
    $line = new OrderLine(
        productId: new ProductId('p'),
        vendorId:  new VendorId('v'),
        quantity:  1,
        unitPrice: new Money(300, 'UAH'),
    );

    expect($line->getLineTotal()->getAmount())->toBe(300);
});

test('rejects quantity of zero', function () {
    expect(fn () => new OrderLine(new ProductId('p'), new VendorId('v'), 0, new Money(100, 'UAH')))
        ->toThrow(OrderException::class);
});

test('rejects negative quantity', function () {
    expect(fn () => new OrderLine(new ProductId('p'), new VendorId('v'), -1, new Money(100, 'UAH')))
        ->toThrow(OrderException::class);
});

test('exposes product id', function () {
    $productId = new ProductId('prod-123');
    $line      = new OrderLine($productId, new VendorId('v'), 1, new Money(100, 'UAH'));

    expect($line->getProductId()->getValue())->toBe('prod-123');
});

test('exposes vendor id', function () {
    $vendorId = new VendorId('vendor-456');
    $line     = new OrderLine(new ProductId('p'), $vendorId, 1, new Money(100, 'UAH'));

    expect($line->getVendorId()->getValue())->toBe('vendor-456');
});

test('line total currency matches unit price currency', function () {
    $line = new OrderLine(
        productId: new ProductId('p'),
        vendorId:  new VendorId('v'),
        quantity:  3,
        unitPrice: new Money(200, 'USD'),
    );

    expect($line->getLineTotal()->getCurrency())->toBe('USD');
});
