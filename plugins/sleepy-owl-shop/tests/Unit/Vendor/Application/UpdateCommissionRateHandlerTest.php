<?php

declare(strict_types=1);

use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Application\Command\RegisterVendorCommand;
use SleepyOwl\Vendor\Application\Command\RegisterVendorHandler;
use SleepyOwl\Vendor\Application\Command\UpdateCommissionRateCommand;
use SleepyOwl\Vendor\Application\Command\UpdateCommissionRateHandler;
use SleepyOwl\Vendor\Domain\Event\CommissionRateUpdated;
use SleepyOwl\Vendor\Domain\Exception\VendorNotFoundException;

beforeEach(function () {
    $register      = new RegisterVendorHandler($this->repo, $this->bus);
    $this->handler = new UpdateCommissionRateHandler($this->repo, $this->bus);

    ($register)(new RegisterVendorCommand('vendor-1', 'Test Shop', 10));
    $this->bus->dispatch([]);
});

test('commission rate is updated on vendor', function () {
    ($this->handler)(new UpdateCommissionRateCommand('vendor-1', 25.5));

    expect($this->repo->findById(new VendorId('vendor-1'))->getCommissionRate())
        ->toEqual(new CommissionRate(25.5));
});

test('update commission rate dispatches CommissionRateUpdated event', function () {
    ($this->handler)(new UpdateCommissionRateCommand('vendor-1', 15.0));

    expect($this->bus->hasDispatched(CommissionRateUpdated::class))->toBeTrue();
});

test('update commission rate throws when vendor not found', function () {
    expect(fn () => ($this->handler)(new UpdateCommissionRateCommand('no-such-vendor', 10.0)))
        ->toThrow(VendorNotFoundException::class);
});
