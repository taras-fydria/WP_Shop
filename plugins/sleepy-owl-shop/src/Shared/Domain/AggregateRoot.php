<?php

namespace SleepyOwl\Shared\Domain;

use SleepyOwl\Shared\Domain\Events\DomainEvent;

abstract class AggregateRoot
{
    /**
     * @var DomainEvent[]
     */
    private array $events = [];

    protected function raiseEvent(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events       = $this->events;
        $this->events = [];
        return $events;
    }
}