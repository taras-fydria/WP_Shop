<?php

declare(strict_types=1);

use SleepyOwl\Cart\Domain\Model\Entity\CartItem;
use SleepyOwl\Cart\Domain\Model\ValueObject\Quantity;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

function makeCartItem(int $qty = 2, int $price = 500): CartItem
{
    return new CartItem(
        productId: new ProductId('p1'),
        vendorId:  new VendorId('v1'),
        quantity:  new Quantity($qty),
        unitPrice: new Money($price, 'UAH'),
    );
}

test('exposes all fields', function () {
    $item = makeCartItem();

    expect($item->getProductId()->getValue())->toBe('p1')
        ->and($item->getVendorId()->getValue())->toBe('v1')
        ->and($item->getQuantity()->getValue())->toBe(2)
        ->and($item->getUnitPrice()->getAmount())->toBe(500);
});

test('line total is quantity times unit price', function () {
    $item = makeCartItem(qty: 3, price: 200);

    expect($item->getLineTotal()->getAmount())->toBe(600);
});

test('mergeQuantity returns new item with summed quantity', function () {
    $item   = makeCartItem(qty: 2);
    $merged = $item->mergeQuantity(new Quantity(3));

    expect($merged->getQuantity()->getValue())->toBe(5);
});

test('mergeQuantity does not mutate original', function () {
    $item = makeCartItem(qty: 2);
    $item->mergeQuantity(new Quantity(3));

    expect($item->getQuantity()->getValue())->toBe(2);
});

test('mergeQuantity returns new instance', function () {
    $item   = makeCartItem(qty: 2);
    $merged = $item->mergeQuantity(new Quantity(1));

    expect($merged)->not->toBe($item);
});

test('withQuantity returns new item with replaced quantity', function () {
    $item    = makeCartItem(qty: 2);
    $updated = $item->withQuantity(new Quantity(7));

    expect($updated->getQuantity()->getValue())->toBe(7);
});

test('withQuantity preserves product and price', function () {
    $item    = makeCartItem(qty: 2, price: 300);
    $updated = $item->withQuantity(new Quantity(5));

    expect($updated->getProductId()->getValue())->toBe('p1')
        ->and($updated->getUnitPrice()->getAmount())->toBe(300);
});
