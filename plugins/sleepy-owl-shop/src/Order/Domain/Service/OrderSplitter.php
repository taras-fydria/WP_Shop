<?php

declare(strict_types=1);

namespace SleepyOwl\Order\Domain\Service;

use SleepyOwl\Order\Domain\Model\Entity\VendorSubOrder;
use SleepyOwl\Order\Domain\Model\ValueObject\OrderLine;
use SleepyOwl\Order\Domain\Model\ValueObject\SubOrderId;
use SleepyOwl\Order\Domain\Service\CommissionEngineInterface;

final class OrderSplitter
{
    public function __construct(private readonly CommissionEngineInterface $commissionEngine) {}

    /**
     * @param  OrderLine[]      $lines
     * @return VendorSubOrder[]
     */
    public function split(array $lines): array
    {
        $groups = [];
        foreach ($lines as $line) {
            $groups[$line->getVendorId()->getValue()][] = $line;
        }

        $subOrders = [];
        foreach ($groups as $vendorLines) {
            $vendorId    = $vendorLines[0]->getVendorId();
            $subOrders[] = new VendorSubOrder(
                id:         SubOrderId::generate(),
                vendorId:   $vendorId,
                lines:      $vendorLines,
                commissionRate: $this->commissionEngine->calculateFor($vendorId),
            );
        }

        return $subOrders;
    }
}
