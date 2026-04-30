<?php

declare(strict_types=1);

namespace SleepyOwl\Catalog\Domain\Repository;

use SleepyOwl\Catalog\Domain\Model\Aggregate\Product;
use SleepyOwl\Catalog\Domain\Model\ValueObject\ProductId;

interface ProductRepositoryInterface
{
    public function findById(ProductId $id): ?Product;

    public function add(Product $product): void;

    public function update(Product $product): void;
}