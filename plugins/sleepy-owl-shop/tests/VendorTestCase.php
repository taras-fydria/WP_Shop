<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use SleepyOwl\Shared\Application\EventBusInterface;
use SleepyOwl\Vendor\Domain\Repository\VendorRepositoryInterface;
use Tests\Fake\Shared\CollectingEventBus;
use Tests\Fake\Vendor\InMemoryVendorRepository;

abstract class VendorTestCase extends TestCase
{
    protected VendorRepositoryInterface $repo;
    protected EventBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new InMemoryVendorRepository();
        $this->bus  = new CollectingEventBus();
    }
}