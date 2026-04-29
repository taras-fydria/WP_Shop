<?php

declare(strict_types=1);

namespace SleepyOwl\Shared\Domain\Events;

abstract readonly class AbstractDomainEvent implements DomainEvent
{
    public \DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->occurredAt = new \DateTimeImmutable();
    }
}