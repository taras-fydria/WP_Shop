<?php

declare(strict_types=1);

namespace SleepyOwl\Shared\Application;

interface EventBusInterface
{
    public function dispatch(array $events): void;

    public function getDispatched(): array;

    public function hasDispatched(string $eventClass): bool;

    public function getDispatchedOfType(string $eventClass): array;
}