<?php

namespace SleepyOwl\Shared\Domain\Events;

trait HasDomainEvent
{
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