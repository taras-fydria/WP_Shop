<?php

declare(strict_types=1);

use SleepyOwl\Vendor\Application\Command\RegisterVendorCommand;
use SleepyOwl\Vendor\Application\Command\RegisterVendorHandler;
use SleepyOwl\Vendor\Application\DTO\VendorReadModel;
use SleepyOwl\Vendor\Application\Query\GetVendorHandler;
use SleepyOwl\Vendor\Application\Query\GetVendorQuery;

beforeEach(function () {
    $register = new RegisterVendorHandler($this->repo, $this->bus);
    ($register)(new RegisterVendorCommand('vendor-1', 'Test Shop', 10));
    $this->bus->dispatch([]);

    $this->handler = new GetVendorHandler($this->repo);
});

test('returns vendor read model by id', function () {
    $result = ($this->handler)(new GetVendorQuery('vendor-1'));

    expect($result)->toBeInstanceOf(VendorReadModel::class)
        ->and($result->id)->toBe('vendor-1')
        ->and($result->businessName)->toBe('Test Shop')
        ->and($result->status)->toBe('pending')
        ->and($result->commissionRate)->toBe(10);
});

test('returns null when vendor not found', function () {
    $result = ($this->handler)(new GetVendorQuery('no-such-vendor'));

    expect($result)->toBeNull();
});