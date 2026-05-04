<?php

declare(strict_types=1);

namespace Tests\Fake\Vendor;

use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;
use SleepyOwl\Vendor\Domain\Repository\VendorRepositoryInterface;

final class InMemoryVendorRepository implements VendorRepositoryInterface
{
    private $vendors = [];

    public function findById(VendorId $id): ?Vendor
    {
        return $this->vendors[$id->getValue()] ?? null;
    }

    public function findAll(?VendorStatus $status = null): array
    {
        if ($status === null) {
            return array_values($this->vendors);
        }

        return array_values(array_filter(
            $this->vendors,
            fn(Vendor $v) => $v->getStatus() === $status,
        ));
    }

    public function add(Vendor $vendor): void
    {
        $key = $vendor->getId()->getValue();
        if (isset($this->vendors[$key])) {
            throw new \RuntimeException("Vendor '{$key}' already exists.");
        }
        $this->vendors[$key] = $vendor;
    }

    public function update(Vendor $vendor): void
    {
        $key = $vendor->getId()->getValue();
        if (!isset($this->vendors[$key])) {
            throw new \RuntimeException("Vendor '{$key}' not found.");
        }
        $this->vendors[$key] = $vendor;
    }

    public function delete(VendorId $id): void
    {
        $key = $id->getValue();
        if (!isset($this->vendors[$key])) {
            throw new \RuntimeException("Vendor '{$key}' not found.");
        }
        unset($this->vendors[$key]);
    }
}
