# Fake (In-Memory) Repositories

**Files:** `tests/Fake/{Context}/InMemory{Aggregate}Repository.php`

---

## Purpose

Implement domain repository interfaces using an in-memory array. Allow command handler tests to run without a database or WordPress. Test-only — never used in production.

---

## Location

```
tests/
└── Fake/
    ├── Shared/
    │   └── CollectingEventBus.php
    ├── Vendor/
    │   └── InMemoryVendorRepository.php
    ├── Catalog/
    │   └── InMemoryProductRepository.php
    ├── Cart/
    │   └── InMemoryCartRepository.php
    ├── Order/
    │   └── InMemoryOrderRepository.php
    ├── Payment/
    │   └── InMemoryPayoutRepository.php
    ├── Shipping/
    │   └── InMemoryShipmentRepository.php
    └── Review/
        └── InMemoryReviewRepository.php
```

---

## Contract

| Fake | Implements |
|------|-----------|
| `InMemoryVendorRepository` | `VendorRepositoryInterface` |
| `InMemoryProductRepository` | `ProductRepositoryInterface` |
| `InMemoryCartRepository` | `CartRepositoryInterface` |
| `InMemoryOrderRepository` | `OrderRepositoryInterface` |
| `InMemoryPayoutRepository` | `PayoutRepositoryInterface` |
| `InMemoryShipmentRepository` | `ShipmentRepositoryInterface` |
| `InMemoryReviewRepository` | `ReviewRepositoryInterface` |

---

## Behaviour

- `findById` — returns stored aggregate or `null`
- `findByBuyerRef` / `findBySubOrderId` — linear scan, first match or `null`
- `add` — stores by ID; throws `\RuntimeException` if already exists
- `update` — overwrites by ID; throws `\RuntimeException` if not found
- `delete` (Cart only) — removes by ID

---

## CollectingEventBus

Implements `EventBusInterface`. Collects dispatched events into an array for assertion — no handlers invoked.

```php
final class CollectingEventBus implements EventBusInterface
{
    private array $dispatched = [];

    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatched[] = $event;
        }
    }

    public function getDispatched(): array
    {
        return $this->dispatched;
    }
}
```

---

## Usage in handler tests

```php
$vendors  = new InMemoryVendorRepository();
$eventBus = new CollectingEventBus();
$handler  = new ApproveVendorHandler($vendors, $eventBus);

$vendor = Vendor::register(new VendorId('v-1'), 'Shop', new CommissionRate(10));
$vendors->add($vendor);
$vendor->releaseEvents();

($handler)(new ApproveVendorCommand(new VendorId('v-1')));

expect($vendors->findById(new VendorId('v-1'))->getStatus())->toBe(VendorStatus::Approved);
expect($eventBus->getDispatched()[0])->toBeInstanceOf(VendorApproved::class);
```
