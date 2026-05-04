<?php

declare(strict_types=1);

use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Event\CommissionRateUpdated;
use SleepyOwl\Vendor\Domain\Event\VendorApproved;
use SleepyOwl\Vendor\Domain\Event\VendorRegistered;
use SleepyOwl\Vendor\Domain\Event\VendorReinstated;
use SleepyOwl\Vendor\Domain\Event\VendorSuspended;
use SleepyOwl\Vendor\Domain\Exception\VendorException;
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;

function makeVendor(int $commissionRate = 10): Vendor
{
    return Vendor::register(
        new VendorId('vendor-1'),
        'Test Shop',
        new CommissionRate($commissionRate),
    );
}

test('register creates vendor in pending status', function () {
    expect(makeVendor()->getStatus())->toBe(VendorStatus::Pending);
});

test('register raises VendorRegistered event', function () {
    $events = makeVendor()->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(VendorRegistered::class);
});

test('approve transitions pending to approved', function () {
    $vendor = makeVendor();
    $vendor->approve();

    expect($vendor->getStatus())->toBe(VendorStatus::Approved);
});

test('approve raises VendorApproved event', function () {
    $vendor = makeVendor();
    $vendor->releaseEvents();
    $vendor->approve();

    $events = $vendor->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(VendorApproved::class);
});

test('cannot approve already approved vendor', function () {
    $vendor = makeVendor();
    $vendor->approve();

    expect(fn () => $vendor->approve())->toThrow(VendorException::class);
});

test('suspend transitions approved to suspended', function () {
    $vendor = makeVendor();
    $vendor->approve();
    $vendor->suspend('policy violation');

    expect($vendor->getStatus())->toBe(VendorStatus::Suspended);
});

test('suspend raises VendorSuspended event with reason', function () {
    $vendor = makeVendor();
    $vendor->approve();
    $vendor->releaseEvents();
    $vendor->suspend('policy violation');

    $events = $vendor->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(VendorSuspended::class)
        ->and($events[0]->reason)->toBe('policy violation');
});

test('cannot suspend already suspended vendor', function () {
    $vendor = makeVendor();
    $vendor->approve();
    $vendor->suspend('reason');

    expect(fn () => $vendor->suspend('again'))->toThrow(VendorException::class);
});

test('reinstate transitions suspended to approved', function () {
    $vendor = makeVendor();
    $vendor->approve();
    $vendor->suspend('reason');
    $vendor->reinstate();

    expect($vendor->getStatus())->toBe(VendorStatus::Approved);
});

test('reinstate raises VendorReinstated event', function () {
    $vendor = makeVendor();
    $vendor->approve();
    $vendor->suspend('reason');
    $vendor->releaseEvents();
    $vendor->reinstate();

    $events = $vendor->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(VendorReinstated::class);
});

test('cannot reinstate non-suspended vendor', function () {
    $vendor = makeVendor();
    $vendor->approve();

    expect(fn () => $vendor->reinstate())->toThrow(VendorException::class);
});

test('updateCommissionRate raises CommissionRateUpdated event', function () {
    $vendor = makeVendor();
    $vendor->releaseEvents();
    $vendor->updateCommissionRate(new CommissionRate(15));

    $events = $vendor->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(CommissionRateUpdated::class)
        ->and($events[0]->newRate->getRate())->toBe(15);
});

test('updateCommissionRate updates the stored rate', function () {
    $vendor = makeVendor(10);
    $vendor->updateCommissionRate(new CommissionRate(20));

    expect($vendor->getCommissionRate()->getRate())->toBe(20);
});

test('releaseEvents clears event buffer', function () {
    $vendor = makeVendor();
    $vendor->releaseEvents();

    expect($vendor->releaseEvents())->toBeEmpty();
});

test('reconstitute restores vendor state without raising events', function () {
    $createdAt = new DateTimeImmutable('2024-01-15 10:00:00');

    $vendor = Vendor::reconstitute(
        id: new VendorId('abc-123'),
        businessName: 'Restored Shop',
        status: VendorStatus::Approved,
        commissionRate: new CommissionRate(15),
        createdAt: $createdAt,
    );

    expect($vendor->getId()->getValue())->toBe('abc-123')
        ->and($vendor->getBusinessName())->toBe('Restored Shop')
        ->and($vendor->getStatus())->toBe(VendorStatus::Approved)
        ->and($vendor->getCommissionRate()->getRate())->toBe(15)
        ->and($vendor->getCreatedAt())->toEqual($createdAt)
        ->and($vendor->releaseEvents())->toBeEmpty();
});