<?php

declare(strict_types=1);

use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Application\Command\RegisterVendorCommand;
use SleepyOwl\Vendor\Application\Command\RegisterVendorHandler;
use SleepyOwl\Vendor\Application\Command\SuspendVendorCommand;
use SleepyOwl\Vendor\Application\Command\SuspendVendorHandler;
use SleepyOwl\Vendor\Domain\Event\VendorSuspended;
use SleepyOwl\Vendor\Domain\Exception\VendorException;
use SleepyOwl\Vendor\Domain\Exception\VendorNotFoundException;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;

beforeEach(function () {
    $register      = new RegisterVendorHandler($this->repo, $this->bus);
    $this->handler = new SuspendVendorHandler($this->repo, $this->bus);

    ($register)(new RegisterVendorCommand('vendor-1', 'Test Shop', 10));
});

test('suspended vendor has suspended status', function () {
    ($this->handler)(new SuspendVendorCommand('vendor-1', 'Policy violation'));

    expect($this->repo->findById(new VendorId('vendor-1'))->getStatus())
        ->toBe(VendorStatus::Suspended);
});

test('suspend dispatches VendorSuspended event', function () {
    ($this->handler)(new SuspendVendorCommand('vendor-1', 'Policy violation'));

    expect($this->bus->hasDispatched(VendorSuspended::class))->toBeTrue();
});

test('suspend throws when vendor not found', function () {
    expect(fn () => ($this->handler)(new SuspendVendorCommand('no-such-vendor', 'reason')))
        ->toThrow(VendorNotFoundException::class);
});

test('suspend throws when vendor already suspended', function () {
    ($this->handler)(new SuspendVendorCommand('vendor-1', 'first reason'));

    expect(fn () => ($this->handler)(new SuspendVendorCommand('vendor-1', 'second reason')))
        ->toThrow(VendorException::class);
});
