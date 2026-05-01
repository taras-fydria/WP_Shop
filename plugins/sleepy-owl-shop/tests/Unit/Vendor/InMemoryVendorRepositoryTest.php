<?php

declare(strict_types=1);

use Mockery\MockInterface;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;
use SleepyOwl\Vendor\Domain\Repository\VendorRepositoryInterface;
use Tests\Fake\Vendor\InMemoryVendorRepository;

afterEach(fn() => Mockery::close());


function getRepository(): VendorRepositoryInterface
{
    return new InMemoryVendorRepository();
}

function mockVendor(string $id): Vendor&MockInterface
{
    $vendor = Mockery::mock(Vendor::class);
    $vendor->allows('getId')->andReturn(new VendorId($id));

    return $vendor;
}

test('findById returns null when store is empty', function () {
    $repo = getRepository();

    expect($repo->findById(new VendorId('vendor-1')))->toBeNull();
});

test('findById returns vendor after add', function () {
    $repo   = getRepository();
    $vendor = mockVendor('vendor-1');

    $repo->add($vendor);

    expect($repo->findById(new VendorId('vendor-1')))->toBe($vendor);
});

test('add stores vendor', function () {
    $repo = getRepository();

    $repo->add(mockVendor('vendor-1'));

    expect($repo->findById(new VendorId('vendor-1')))->not->toBeNull();
});

test('add throws when id already exists', function () {
    $repo = getRepository();
    $repo->add(mockVendor('vendor-1'));

    expect(fn() => $repo->add(mockVendor('vendor-1')))->toThrow(\RuntimeException::class);
});

test('update overwrites stored vendor', function () {
    $repo   = getRepository();
    $vendor = mockVendor('vendor-1');
    $repo->add($vendor);

    $updated = mockVendor('vendor-1');
    $repo->update($updated);

    expect($repo->findById(new VendorId('vendor-1')))->toBe($updated);
});

test('update throws when id not found', function () {
    $repo = getRepository();

    expect(fn() => $repo->update(mockVendor('vendor-1')))->toThrow(\RuntimeException::class);
});

test('delete removes vendor', function () {
    $repo = getRepository();
    $repo->add(mockVendor('vendor-1'));

    $repo->delete(new VendorId('vendor-1'));

    expect($repo->findById(new VendorId('vendor-1')))->toBeNull();
});

test('delete throws when id not found', function () {
    $repo = getRepository();

    expect(fn() => $repo->delete(new VendorId('vendor-1')))->toThrow(\RuntimeException::class);
});

test('findById returns null after delete', function () {
    $repo = getRepository();
    $repo->add(mockVendor('vendor-1'));
    $repo->delete(new VendorId('vendor-1'));

    expect($repo->findById(new VendorId('vendor-1')))->toBeNull();
});