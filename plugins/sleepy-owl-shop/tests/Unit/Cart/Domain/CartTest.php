<?php

declare(strict_types=1);

use SleepyOwl\Cart\Domain\Event\CartCheckedOut;
use SleepyOwl\Cart\Domain\Event\CartCleared;
use SleepyOwl\Cart\Domain\Event\CartItemQuantityUpdated;
use SleepyOwl\Cart\Domain\Event\ItemAddedToCart;
use SleepyOwl\Cart\Domain\Event\ItemRemovedFromCart;
use SleepyOwl\Cart\Domain\Exception\CartException;
use SleepyOwl\Cart\Domain\Model\Aggregate\Cart;
use SleepyOwl\Cart\Domain\Model\ValueObject\CartId;
use SleepyOwl\Cart\Domain\Model\ValueObject\Quantity;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;
use SleepyOwl\Shared\Domain\Model\ValueObject\Money;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

function makeCart(): Cart
{
    return new Cart(new CartId('cart-1'), 'buyer-42');
}

function p(string $id = 'p1'): ProductId { return new ProductId($id); }
function v(string $id = 'v1'): VendorId  { return new VendorId($id); }
function qty(int $n = 1): Quantity        { return new Quantity($n); }
function uah(int $n = 100): Money         { return new Money($n, 'UAH'); }

// --- addItem ---

test('addItem adds new item to cart', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(500), qty(2));

    expect($cart->getItems())->toHaveCount(1);
});

test('addItem raises ItemAddedToCart event', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(500), qty(2));

    $events = $cart->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ItemAddedToCart::class)
        ->and($events[0]->productId->getValue())->toBe('p1')
        ->and($events[0]->newQuantity->getValue())->toBe(2);
});

test('addItem same product merges quantity', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(500), qty(2));
    $cart->addItem(p(), v(), uah(500), qty(3));

    expect($cart->getItems())->toHaveCount(1)
        ->and($cart->getItems()[0]->getQuantity()->getValue())->toBe(5);
});

test('addItem same product raises event with merged quantity', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(500), qty(2));
    $cart->releaseEvents();
    $cart->addItem(p(), v(), uah(500), qty(3));

    $events = $cart->releaseEvents();

    expect($events[0]->newQuantity->getValue())->toBe(5);
});

test('addItem different products creates separate items', function () {
    $cart = makeCart();
    $cart->addItem(p('p1'), v(), uah(500), qty(1));
    $cart->addItem(p('p2'), v(), uah(300), qty(2));

    expect($cart->getItems())->toHaveCount(2);
});

// --- removeItem ---

test('removeItem removes existing item', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(100), qty(1));
    $cart->releaseEvents();
    $cart->removeItem(p());

    expect($cart->getItems())->toBeEmpty();
});

test('removeItem raises ItemRemovedFromCart event', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(100), qty(1));
    $cart->releaseEvents();
    $cart->removeItem(p());

    $events = $cart->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(ItemRemovedFromCart::class)
        ->and($events[0]->productId->getValue())->toBe('p1');
});

test('removeItem throws when product not in cart', function () {
    $cart = makeCart();

    expect(fn () => $cart->removeItem(p('missing')))->toThrow(CartException::class);
});

// --- updateQuantity ---

test('updateQuantity changes item quantity', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(100), qty(1));
    $cart->releaseEvents();
    $cart->updateQuantity(p(), qty(5));

    expect($cart->getItems()[0]->getQuantity()->getValue())->toBe(5);
});

test('updateQuantity raises CartItemQuantityUpdated event', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(100), qty(1));
    $cart->releaseEvents();
    $cart->updateQuantity(p(), qty(5));

    $events = $cart->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(CartItemQuantityUpdated::class)
        ->and($events[0]->newQuantity->getValue())->toBe(5);
});

test('updateQuantity throws when product not in cart', function () {
    $cart = makeCart();

    expect(fn () => $cart->updateQuantity(p('missing'), qty(3)))->toThrow(CartException::class);
});

// --- clear ---

test('clear empties all items', function () {
    $cart = makeCart();
    $cart->addItem(p('p1'), v(), uah(100), qty(1));
    $cart->addItem(p('p2'), v(), uah(200), qty(2));
    $cart->releaseEvents();
    $cart->clear();

    expect($cart->getItems())->toBeEmpty();
});

test('clear raises CartCleared event', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(100), qty(1));
    $cart->releaseEvents();
    $cart->clear();

    $events = $cart->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(CartCleared::class);
});

// --- checkout ---

test('checkout raises CartCheckedOut event', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(500), qty(2));
    $cart->releaseEvents();
    $cart->checkout();

    $events = $cart->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(CartCheckedOut::class);
});

test('checkout event carries correct buyer ref', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(500), qty(1));
    $cart->releaseEvents();
    $cart->checkout();

    $event = $cart->releaseEvents()[0];

    expect($event->buyerRef)->toBe('buyer-42');
});

test('checkout event carries items snapshot', function () {
    $cart = makeCart();
    $cart->addItem(p('p1'), v(), uah(500), qty(2));
    $cart->addItem(p('p2'), v(), uah(300), qty(1));
    $cart->releaseEvents();
    $cart->checkout();

    $event = $cart->releaseEvents()[0];

    expect($event->items)->toHaveCount(2);
});

test('checkout throws when cart is empty', function () {
    $cart = makeCart();

    expect(fn () => $cart->checkout())->toThrow(CartException::class);
});

test('checkout does not clear cart automatically', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(100), qty(1));
    $cart->releaseEvents();
    $cart->checkout();

    expect($cart->getItems())->toHaveCount(1);
});

// --- misc ---

test('releaseEvents clears event buffer', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(100), qty(1));

    $cart->releaseEvents();

    expect($cart->releaseEvents())->toBeEmpty();
});

test('isEmpty returns true for empty cart', function () {
    expect(makeCart()->isEmpty())->toBeTrue();
});

test('isEmpty returns false after adding item', function () {
    $cart = makeCart();
    $cart->addItem(p(), v(), uah(100), qty(1));

    expect($cart->isEmpty())->toBeFalse();
});
