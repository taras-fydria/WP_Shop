<?php

declare(strict_types=1);

use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Application\Command\RegisterVendorCommand;
use SleepyOwl\Vendor\Application\Command\RegisterVendorHandler;
use SleepyOwl\Vendor\Domain\Event\VendorRegistered;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;

beforeEach(function () {
    $this->handler = new RegisterVendorHandler($this->repo, $this->bus);
});

test('registered vendor is stored in repository', function () {
    ($this->handler)(new RegisterVendorCommand(
        vendorId: 'vendor-1',
        businessName: 'Test Shop',
        commissionRate: 10,
    ));

    expect($this->repo->findById(new VendorId('vendor-1')))->not->toBeNull();
});

test('registered vendor has pending status', function () {
    ($this->handler)(new RegisterVendorCommand(
        vendorId: 'vendor-1',
        businessName: 'Test Shop',
        commissionRate: 10,
    ));

    expect($this->repo->findById(new VendorId('vendor-1'))->getStatus())->toBe(VendorStatus::Pending);
});

test('register dispatches VendorRegistered event', function () {
    ($this->handler)(new RegisterVendorCommand(
        vendorId: 'vendor-1',
        businessName: 'Test Shop',
        commissionRate: 10,
    ));

    expect($this->bus->getDispatched())->toHaveCount(1)
        ->and($this->bus->getDispatched()[0])->toBeInstanceOf(VendorRegistered::class);
});

test('registering duplicate vendor id throws', function () {
    $command = new RegisterVendorCommand(
        vendorId: 'vendor-1',
        businessName: 'Test Shop',
        commissionRate: 10,
    );

    ($this->handler)($command);

    expect(fn () => ($this->handler)($command))->toThrow(\RuntimeException::class);
});