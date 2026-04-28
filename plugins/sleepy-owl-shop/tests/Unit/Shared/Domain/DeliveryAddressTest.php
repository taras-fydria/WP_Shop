<?php

declare(strict_types=1);

use SleepyOwl\Shared\Domain\DeliveryAddress;
use SleepyOwl\Shared\Domain\Errors\DeliveryAddressException;

test('creates delivery address with all fields', function () {
    $address = new DeliveryAddress(
        country: 'UA',
        city: 'Kyiv',
        street: 'Khreshchatyk 1',
        postalCode: '01001',
    );

    expect($address->getCity())->toBe('Kyiv')
        ->and($address->getStreet())->toBe('Khreshchatyk 1')
        ->and($address->getPostalCode())->toBe('01001')
        ->and($address->getCountry())->toBe('UA');
});

test('rejects empty city', function () {
    expect(fn () => new DeliveryAddress('', 'Khreshchatyk 1', '01001', 'UA'))
        ->toThrow(DeliveryAddressException::class);
});

test('rejects empty street', function () {
    expect(fn () => new DeliveryAddress('Kyiv', '', '01001', 'UA'))
        ->toThrow(DeliveryAddressException::class);
});

test('rejects empty country', function () {
    expect(fn () => new DeliveryAddress('Kyiv', 'Khreshchatyk 1', '01001', ''))
        ->toThrow(DeliveryAddressException::class);
});

test('is equal to address with same fields', function () {
    $a = new DeliveryAddress('Kyiv', 'Khreshchatyk 1', '01001', 'UA');
    $b = new DeliveryAddress('Kyiv', 'Khreshchatyk 1', '01001', 'UA');

    expect($a->equals($b))->toBeTrue();
});

test('is not equal when city differs', function () {
    $a = new DeliveryAddress('Kyiv', 'Khreshchatyk 1', '01001', 'UA');
    $b = new DeliveryAddress('Lviv', 'Khreshchatyk 1', '01001', 'UA');

    expect($a->equals($b))->toBeFalse();
});
