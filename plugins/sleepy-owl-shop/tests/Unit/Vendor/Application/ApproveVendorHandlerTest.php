<?php

declare(strict_types=1);

use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Application\Command\ApproveVendorCommand;
use SleepyOwl\Vendor\Application\Command\ApproveVendorHandler;
use SleepyOwl\Vendor\Application\Command\RegisterVendorCommand;
use SleepyOwl\Vendor\Application\Command\RegisterVendorHandler;
use SleepyOwl\Vendor\Domain\Event\VendorApproved;
use SleepyOwl\Vendor\Domain\Exception\VendorException;
use SleepyOwl\Vendor\Domain\Exception\VendorNotFoundException;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;

beforeEach(function () {
    $register      = new RegisterVendorHandler($this->repo, $this->bus);
    $this->handler = new ApproveVendorHandler($this->repo, $this->bus);

    ($register)(new RegisterVendorCommand('vendor-1', 'Test Shop', 10));
    $this->bus->dispatch([]); // flush registration events
});

test('approved vendor has approved status', function () {
    ($this->handler)(new ApproveVendorCommand('vendor-1'));

    expect($this->repo->findById(new VendorId('vendor-1'))->getStatus())
        ->toBe(VendorStatus::Approved);
});

test('approve dispatches VendorApproved event', function () {
    ($this->handler)(new ApproveVendorCommand('vendor-1'));

    expect($this->bus->hasDispatched(VendorApproved::class))->toBeTrue();
});

test('approve throws when vendor not found', function () {
    expect(fn () => ($this->handler)(new ApproveVendorCommand('no-such-vendor')))
        ->toThrow(VendorNotFoundException::class);
});

test('approve throws when vendor already approved', function () {
    ($this->handler)(new ApproveVendorCommand('vendor-1'));

    expect(fn () => ($this->handler)(new ApproveVendorCommand('vendor-1')))
        ->toThrow(VendorException::class);
});