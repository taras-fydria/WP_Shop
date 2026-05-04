<?php

declare(strict_types=1);

use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;
use SleepyOwl\Vendor\Infrastructure\Persistence\WpdbVendorRepository;

function makeRepo(): WpdbVendorRepository
{
    global $wpdb;
    return new WpdbVendorRepository($wpdb);
}

function makeTestVendor(string $id = 'abc-123', int $rate = 10): Vendor
{
    $vendor = Vendor::register(new VendorId($id), 'Test Shop', new CommissionRate($rate));
    $vendor->releaseEvents();
    return $vendor;
}

test('persists and retrieves vendor by id', function () {
    $repo   = makeRepo();
    $vendor = makeTestVendor();

    $repo->add($vendor);

    $found = $repo->findById(new VendorId('abc-123'));
    expect($found)->not->toBeNull()
        ->and($found->getBusinessName())->toBe('Test Shop')
        ->and($found->getStatus())->toBe(VendorStatus::Pending)
        ->and($found->getCommissionRate()->getRate())->toBe(10.0);
});

test('returns null for unknown vendor', function () {
    expect(makeRepo()->findById(new VendorId('no-such-id')))->toBeNull();
});

test('update persists status change', function () {
    $repo   = makeRepo();
    $vendor = makeTestVendor();
    $repo->add($vendor);

    $vendor->approve();
    $repo->update($vendor);

    expect($repo->findById($vendor->getId())->getStatus())->toBe(VendorStatus::Approved);
});

test('delete removes vendor', function () {
    $repo   = makeRepo();
    $vendor = makeTestVendor();
    $repo->add($vendor);

    $repo->delete($vendor->getId());

    expect($repo->findById($vendor->getId()))->toBeNull();
});

test('findAll returns all vendors', function () {
    $repo = makeRepo();
    $repo->add(makeTestVendor('v-1'));
    $repo->add(makeTestVendor('v-2'));

    expect($repo->findAll())->toHaveCount(2);
});

test('findAll filters by status', function () {
    $repo    = makeRepo();
    $pending = makeTestVendor('v-1');
    $approved = makeTestVendor('v-2');
    $approved->approve();

    $repo->add($pending);
    $repo->add($approved);

    expect($repo->findAll(VendorStatus::Pending))->toHaveCount(1)
        ->and($repo->findAll(VendorStatus::Approved))->toHaveCount(1);
});