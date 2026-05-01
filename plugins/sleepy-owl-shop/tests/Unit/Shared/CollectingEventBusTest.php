<?php

declare(strict_types=1);

use SleepyOwl\Shared\Application\EventBusInterface;
use SleepyOwl\Shared\Domain\Events\DomainEvent;
use Tests\Fake\Shared\CollectingEventBus;

function getEventBus(): EventBusInterface
{
    return new CollectingEventBus();
}

test('dispatched events are empty initially', function () {
    $bus = getEventBus();

    expect($bus->getDispatched())->toBe([]);
});

test('dispatch stores events', function () {
    $bus   = getEventBus();
    $event = new class implements DomainEvent {};

    $bus->dispatch([$event]);

    expect($bus->getDispatched())->toHaveCount(1)
        ->and($bus->getDispatched()[0])->toBe($event);
});

test('dispatch accumulates events across multiple calls', function () {
    $bus    = getEventBus();
    $first  = new class implements DomainEvent {};
    $second = new class implements DomainEvent {};

    $bus->dispatch([$first]);
    $bus->dispatch([$second]);

    expect($bus->getDispatched())->toHaveCount(2);
});

test('dispatch with empty array adds nothing', function () {
    $bus = getEventBus();

    $bus->dispatch([]);

    expect($bus->getDispatched())->toBe([]);
});

test('hasDispatched returns true when event class was dispatched', function () {
    $bus   = getEventBus();
    $event = new class implements DomainEvent {};

    $bus->dispatch([$event]);

    expect($bus->hasDispatched($event::class))->toBeTrue();
});

test('hasDispatched returns false when event class was not dispatched', function () {
    $bus = getEventBus();

    expect($bus->hasDispatched(DomainEvent::class))->toBeFalse();
});

test('getDispatchedOfType returns only matching events', function () {
    $bus = getEventBus();

    $eventA = new class implements DomainEvent {};
    $eventB = new class implements DomainEvent {};

    $bus->dispatch([$eventA, $eventB]);

    $result = $bus->getDispatchedOfType($eventA::class);

    expect($result)->toHaveCount(1)
        ->and($result[0])->toBe($eventA);
});

test('getDispatchedOfType returns empty array when no match', function () {
    $bus = getEventBus();

    expect($bus->getDispatchedOfType(DomainEvent::class))->toBe([]);
});