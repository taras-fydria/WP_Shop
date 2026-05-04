<?php

declare(strict_types=1);

namespace SleepyOwl\Vendor\Infrastructure\Persistence;

use DateTimeImmutable;
use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;
use SleepyOwl\Vendor\Domain\Repository\VendorRepositoryInterface;

final class WpdbVendorRepository implements VendorRepositoryInterface
{
    private string $table;

    public function __construct(private readonly \wpdb $wpdb)
    {
        $this->table = $wpdb->prefix . 'so_vendors';
    }

    public function findById(VendorId $id): ?Vendor
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %s", $id->getValue()),
            ARRAY_A,
        );

        return $row ? $this->hydrate($row) : null;
    }

    public function findAll(?VendorStatus $status = null): array
    {
        $rows = $status !== null
            ? $this->wpdb->get_results(
                $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE status = %s", $status->value),
                ARRAY_A,
            )
            : $this->wpdb->get_results("SELECT * FROM {$this->table}", ARRAY_A);

        return array_map($this->hydrate(...), $rows);
    }

    public function add(Vendor $vendor): void
    {
        $this->wpdb->insert($this->table, $this->extract($vendor));
    }

    public function update(Vendor $vendor): void
    {
        $this->wpdb->update($this->table, $this->extract($vendor), ['id' => $vendor->getId()->getValue()]);
    }

    public function delete(VendorId $id): void
    {
        $this->wpdb->delete($this->table, ['id' => $id->getValue()]);
    }

    private function extract(Vendor $vendor): array
    {
        return [
            'id'              => $vendor->getId()->getValue(),
            'business_name'   => $vendor->getBusinessName(),
            'status'          => $vendor->getStatus()->value,
            'commission_rate' => $vendor->getCommissionRate()->getRate(),
            'created_at'      => $vendor->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    private function hydrate(array $row): Vendor
    {
        return Vendor::reconstitute(
            id: new VendorId($row['id']),
            businessName: $row['business_name'],
            status: VendorStatus::from($row['status']),
            commissionRate: new CommissionRate((float) $row['commission_rate']),
            createdAt: new DateTimeImmutable($row['created_at']),
        );
    }
}