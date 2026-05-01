<?php

declare(strict_types=1);

namespace Tests\Fake\Shared;

use SleepyOwl\Shared\Application\EventBusInterface;
use SleepyOwl\Shared\Domain\Events\DomainEvent;

final class CollectingEventBus implements EventBusInterface
{
    /**
     * @var DomainEvent[]
     */
    private array $dispatched = [];

    /**
     * @param DomainEvent[] $events
     * @return void
     */
    public function dispatch(array $events): void
    {
        $this->dispatched = [...$this->dispatched, ...(array_map(fn($event) => $event, $events))];
    }

    /**
     * @return DomainEvent[]
     */
    public function getDispatched(): array
    {
        return $this->dispatched;
    }

    public function hasDispatched(string $eventClass): bool
    {
        foreach ($this->dispatched as $event) {
            if ($event instanceof $eventClass) {
                return true;
            }
        }

        return false;
    }

    public function getDispatchedOfType(string $eventClass): array
    {
        return array_values(array_filter(
            $this->dispatched,
            fn ($e) => $e instanceof $eventClass,
        ));
    }
}