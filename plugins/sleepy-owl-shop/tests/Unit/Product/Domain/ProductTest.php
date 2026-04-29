<?php

declare(strict_types=1);

use SleepyOwl\Catalog\Domain\Event\ProductActivated;
use SleepyOwl\Catalog\Domain\Event\ProductCreated;
use SleepyOwl\Catalog\Domain\Event\ProductDeactivated;
use SleepyOwl\Catalog\Domain\Event\ProductOwnershipAssigned;
use SleepyOwl\Catalog\Domain\Event\ProductPriceUpdated;
use SleepyOwl\Catalog\Domain\Exception\CatalogException;
use SleepyOwl\Catalog\Domain\Model\Aggregate\Product;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductStatus;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

function makeProduct(): Product
{
    return Product::create(
        new ProductId('product-1'),
        new VendorId('vendor-1'),
        new Money(1000, 'UAH'),
    );
}

test('create sets status to draft', function () {
    expect(makeProduct()->getStatus())->toBe(ProductStatus::Draft);
});

test('create raises ProductCreated event', function () {
    $events = makeProduct()->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ProductCreated::class);
});

test('activate transitions draft to active', function () {
    $product = makeProduct();
    $product->activate();

    expect($product->getStatus())->toBe(ProductStatus::Active);
});

test('activate raises ProductActivated event', function () {
    $product = makeProduct();
    $product->releaseEvents();
    $product->activate();

    $events = $product->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ProductActivated::class);
});

test('cannot activate already active product', function () {
    $product = makeProduct();
    $product->activate();

    expect(fn () => $product->activate())->toThrow(CatalogException::class);
});

test('activate transitions deactivated to active', function () {
    $product = makeProduct();
    $product->activate();
    $product->deactivate();
    $product->activate();

    expect($product->getStatus())->toBe(ProductStatus::Active);
});

test('deactivate transitions active to deactivated', function () {
    $product = makeProduct();
    $product->activate();
    $product->deactivate();

    expect($product->getStatus())->toBe(ProductStatus::Deactivated);
});

test('deactivate raises ProductDeactivated event', function () {
    $product = makeProduct();
    $product->activate();
    $product->releaseEvents();
    $product->deactivate();

    $events = $product->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ProductDeactivated::class);
});

test('cannot deactivate draft product', function () {
    expect(fn () => makeProduct()->deactivate())->toThrow(CatalogException::class);
});

test('updatePrice raises ProductPriceUpdated event', function () {
    $product = makeProduct();
    $product->releaseEvents();
    $product->updatePrice(new Money(2000, 'UAH'));

    $events = $product->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ProductPriceUpdated::class)
        ->and($events[0]->newPrice->getAmount())->toBe(2000);
});

test('cannot update price of deactivated product', function () {
    $product = makeProduct();
    $product->activate();
    $product->deactivate();

    expect(fn () => $product->updatePrice(new Money(2000, 'UAH')))->toThrow(CatalogException::class);
});

test('assignToVendor raises ProductOwnershipAssigned event', function () {
    $product  = makeProduct();
    $newOwner = new VendorId('vendor-2');
    $product->releaseEvents();
    $product->assignToVendor($newOwner);

    $events = $product->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ProductOwnershipAssigned::class)
        ->and($events[0]->newOwner->getValue())->toBe('vendor-2');
});

test('cannot reassign deactivated product', function () {
    $product = makeProduct();
    $product->activate();
    $product->deactivate();

    expect(fn () => $product->assignToVendor(new VendorId('vendor-2')))->toThrow(CatalogException::class);
});

test('releaseEvents clears event buffer', function () {
    $product = makeProduct();
    $product->releaseEvents();

    expect($product->releaseEvents())->toBeEmpty();
});