<?php

declare(strict_types=1);

use SleepyOwl\Vendor\Application\Command\ApproveVendorCommand;
use SleepyOwl\Vendor\Application\Command\ApproveVendorHandler;
use SleepyOwl\Vendor\Application\Command\RegisterVendorCommand;
use SleepyOwl\Vendor\Application\Command\RegisterVendorHandler;
use SleepyOwl\Vendor\Application\DTO\VendorReadModel;
use SleepyOwl\Vendor\Application\Query\ListVendorsHandler;
use SleepyOwl\Vendor\Application\Query\ListVendorsQuery;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;

beforeEach(function () {
    $register = new RegisterVendorHandler($this->repo, $this->bus);
    ($register)(new RegisterVendorCommand('vendor-1', 'Pending Shop', 10));
    ($register)(new RegisterVendorCommand('vendor-2', 'Approved Shop', 15));
    $this->bus->dispatch([]);

    $approve = new ApproveVendorHandler($this->repo, $this->bus);
    ($approve)(new ApproveVendorCommand('vendor-2'));
    $this->bus->dispatch([]);

    $this->handler = new ListVendorsHandler($this->repo);
});

test('returns all vendors when no status filter', function () {
    $result = ($this->handler)(new ListVendorsQuery());

    expect($result)->toHaveCount(2)
        ->and($result[0])->toBeInstanceOf(VendorReadModel::class);
});

test('filters by pending status', function () {
    $result = ($this->handler)(new ListVendorsQuery(VendorStatus::Pending));

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe('vendor-1');
});

test('filters by approved status', function () {
    $result = ($this->handler)(new ListVendorsQuery(VendorStatus::Approved));

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe('vendor-2');
});

test('returns empty array when no vendors match filter', function () {
    $result = ($this->handler)(new ListVendorsQuery(VendorStatus::Suspended));

    expect($result)->toBeEmpty();
});