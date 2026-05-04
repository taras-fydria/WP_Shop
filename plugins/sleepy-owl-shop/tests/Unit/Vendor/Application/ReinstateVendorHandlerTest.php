<?php

declare(strict_types=1);

use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Application\Command\RegisterVendorCommand;
use SleepyOwl\Vendor\Application\Command\RegisterVendorHandler;
use SleepyOwl\Vendor\Application\Command\ReinstateVendorCommand;
use SleepyOwl\Vendor\Application\Command\ReinstateVendorHandler;
use SleepyOwl\Vendor\Application\Command\SuspendVendorCommand;
use SleepyOwl\Vendor\Application\Command\SuspendVendorHandler;
use SleepyOwl\Vendor\Domain\Event\VendorReinstated;
use SleepyOwl\Vendor\Domain\Exception\VendorException;
use SleepyOwl\Vendor\Domain\Exception\VendorNotFoundException;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;

beforeEach(function () {
    $register = new RegisterVendorHandler($this->repo, $this->bus);
    $suspend  = new SuspendVendorHandler($this->repo, $this->bus);
    $this->handler = new ReinstateVendorHandler($this->repo, $this->bus);

    ($register)(new RegisterVendorCommand('vendor-1', 'Test Shop', 10));
    ($suspend)(new SuspendVendorCommand('vendor-1', 'Policy violation'));
    $this->bus->dispatch([]);
});

test('reinstated vendor has approved status', function () {
    ($this->handler)(new ReinstateVendorCommand('vendor-1'));

    expect($this->repo->findById(new VendorId('vendor-1'))->getStatus())
        ->toBe(VendorStatus::Approved);
});

test('reinstate dispatches VendorReinstated event', function () {
    ($this->handler)(new ReinstateVendorCommand('vendor-1'));

    expect($this->bus->hasDispatched(VendorReinstated::class))->toBeTrue();
});

test('reinstate throws when vendor not found', function () {
    expect(fn () => ($this->handler)(new ReinstateVendorCommand('no-such-vendor')))
        ->toThrow(VendorNotFoundException::class);
});

test('reinstate throws when vendor is not suspended', function () {
    ($this->handler)(new ReinstateVendorCommand('vendor-1'));

    expect(fn () => ($this->handler)(new ReinstateVendorCommand('vendor-1')))
        ->toThrow(VendorException::class);
});
