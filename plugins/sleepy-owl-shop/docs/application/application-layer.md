# Application Layer (CQRS)

**Layer:** Application  
**Files:** `src/{Context}/Application/{Command|Query|EventHandler}/`

---

## Overview

The application layer orchestrates use cases. It sits between the infrastructure (HTTP controllers, WP hooks) and the domain (aggregates, services). It has no business logic of its own — it loads aggregates via repositories, calls domain methods, collects raised events, and dispatches them to cross-context listeners.

**Write side (Commands):** mutate state — load aggregate → call method → save → dispatch events  
**Read side (Queries):** return data — query DB directly, bypass aggregates, return read models  
**Event handlers:** react to domain events raised by aggregates — cross-context side effects

---

## File structure

```
src/{Context}/Application/
├── Command/
│   ├── {UseCase}Command.php      — immutable DTO (readonly class)
│   └── {UseCase}Handler.php      — __invoke(Command): void
├── Query/
│   ├── {UseCase}Query.php        — immutable DTO
│   └── {UseCase}Handler.php      — __invoke(Query): ReadModel
└── EventHandler/
    └── On{EventName}.php         — __invoke(DomainEvent): void
```

---

## Commands

### Vendor context — `src/Vendor/Application/Command/`

| Command | Handler | Aggregate method |
|---------|---------|-----------------|
| `RegisterVendorCommand` | `RegisterVendorHandler` | `Vendor::register()` |
| `ApproveVendorCommand` | `ApproveVendorHandler` | `Vendor::approve()` |
| `SuspendVendorCommand` | `SuspendVendorHandler` | `Vendor::suspend()` |
| `ReinstateVendorCommand` | `ReinstateVendorHandler` | `Vendor::reinstate()` |
| `UpdateCommissionRateCommand` | `UpdateCommissionRateHandler` | `Vendor::updateCommissionRate()` |

### Catalog context — `src/Catalog/Application/Command/`

| Command | Handler | Aggregate method |
|---------|---------|-----------------|
| `CreateProductCommand` | `CreateProductHandler` | `Product::create()` |
| `ActivateProductCommand` | `ActivateProductHandler` | `Product::activate()` |
| `DeactivateProductCommand` | `DeactivateProductHandler` | `Product::deactivate()` |
| `UpdateProductPriceCommand` | `UpdateProductPriceHandler` | `Product::updatePrice()` |

### Cart context — `src/Cart/Application/Command/`

| Command | Handler | Aggregate method |
|---------|---------|-----------------|
| `AddItemToCartCommand` | `AddItemToCartHandler` | `Cart::addItem()` |
| `RemoveItemFromCartCommand` | `RemoveItemFromCartHandler` | `Cart::removeItem()` |
| `UpdateCartItemQuantityCommand` | `UpdateCartItemQuantityHandler` | `Cart::updateQuantity()` |
| `CheckoutCartCommand` | `CheckoutCartHandler` | `Cart::checkout()` |

### Order context — `src/Order/Application/Command/`

| Command | Handler | Aggregate method |
|---------|---------|-----------------|
| `PlaceOrderCommand` | `PlaceOrderHandler` | `MarketplaceOrder::place()` |
| `SplitOrderCommand` | `SplitOrderHandler` | `MarketplaceOrder::split()` |
| `ConfirmSubOrderCommand` | `ConfirmSubOrderHandler` | `VendorSubOrder::confirm()` |

### Shipping context — `src/Shipping/Application/Command/`

| Command | Handler | Aggregate method |
|---------|---------|-----------------|
| `CreateShipmentCommand` | `CreateShipmentHandler` | `Shipment::create()` |

### Payment context — `src/Payment/Application/Command/`

| Command | Handler | Aggregate method |
|---------|---------|-----------------|
| `InitiatePayoutCommand` | `InitiatePayoutHandler` | `Payout::create()` + `Payout::initiate()` |
| `CompletePayoutCommand` | `CompletePayoutHandler` | `Payout::markCompleted()` |
| `FailPayoutCommand` | `FailPayoutHandler` | `Payout::markFailed()` |

### Review context — `src/Review/Application/Command/`

| Command | Handler | Aggregate method |
|---------|---------|-----------------|
| `SubmitReviewCommand` | `SubmitReviewHandler` | `Review::submit()` |
| `ApproveReviewCommand` | `ApproveReviewHandler` | `Review::approve()` |
| `RejectReviewCommand` | `RejectReviewHandler` | `Review::reject()` |

---

## Command handler pattern

```php
final class ApproveVendorHandler
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendors,
        private readonly EventBusInterface         $eventBus,
    ) {}

    public function __invoke(ApproveVendorCommand $command): void
    {
        $vendor = $this->vendors->findById($command->vendorId);
        if ($vendor === null) {
            throw new VendorNotFoundException($command->vendorId);
        }

        $vendor->approve();

        $this->vendors->update($vendor);
        $this->eventBus->dispatch($vendor->releaseEvents());
    }
}
```

---

## Cross-context event handlers

These listeners react to domain events dispatched by the event bus. Each lives in the context that **reacts**, not the context that raised the event.

| Event | Handler | Location | Action |
|-------|---------|----------|--------|
| `CartCheckedOut` | `OnCartCheckedOut` | `Order/Application/EventHandler/` | Creates `MarketplaceOrder`, dispatches `PlaceOrderCommand` |
| `OrderPaid` | `OnOrderPaid` | `Order/Application/EventHandler/` | Dispatches `SplitOrderCommand` |
| `ShipmentCreated` | `OnShipmentCreated` | `Order/Application/EventHandler/` | Calls `VendorSubOrder::dispatch()`, raises `SubOrderDispatched` |
| `ShipmentDelivered` | `OnShipmentDelivered` | `Order/Application/EventHandler/` | Calls `VendorSubOrder::complete()`, raises `SubOrderCompleted` |
| `SubOrderCompleted` | `OnSubOrderCompleted` | `Order/Application/EventHandler/` | Checks all sub-orders — if all done, calls `MarketplaceOrder::complete()` |
| `SubOrderCompleted` | `OnSubOrderCompletedInitiatePayout` | `Payment/Application/EventHandler/` | Creates `Payout`, dispatches `InitiatePayoutCommand` |

---

## Queries

Queries load aggregates via the existing write repository and map them to read models. No separate read repositories — one repository per context. If raw SQL is needed later for performance (complex joins, pagination), read repos can be introduced then.

### `src/{Context}/Application/Query/`

| Query | Handler | Returns | Consumer |
|-------|---------|---------|----------|
| `GetVendorQuery` | `GetVendorHandler` | `VendorReadModel\|null` | Manager/Vendor — single vendor profile |
| `ListVendorsQuery` | `ListVendorsHandler` | `VendorReadModel[]` | Manager — pending list; Admin — all vendors |
| `GetOrderQuery` | `GetOrderHandler` | `OrderReadModel` | Buyer — sees one order, sub-orders hidden |
| `GetVendorSubOrdersQuery` | `GetVendorSubOrdersHandler` | `SubOrderReadModel[]` | Vendor — sees own sub-orders only |
| `GetVendorProductsQuery` | `GetVendorProductsHandler` | `ProductReadModel[]` | Vendor — own product listing |
| `GetProductsQuery` | `GetProductsHandler` | `ProductReadModel[]` | Buyer — public catalog |
| `GetPayoutsQuery` | `GetPayoutsHandler` | `PayoutReadModel[]` | Vendor — payout history |
| `GetReviewsQuery` | `GetReviewsHandler` | `ReviewReadModel[]` | Public — approved reviews only |

### Query handler pattern

```php
final class GetOrderHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
    ) {}

    public function __invoke(GetOrderQuery $query): ?OrderReadModel
    {
        $order = $this->orders->findById($query->orderId);

        return $order ? OrderReadModel::fromAggregate($order) : null;
    }
}
```

---

## Design notes

- Commands and queries are **immutable readonly DTOs** — no setters, constructed once
- Handlers are **final invokable classes** — `__invoke(Command|Query)`
- Handlers do **not** contain business logic — they delegate to aggregates and domain services
- `EventBusInterface` lives in `Shared/Application/` — dispatched synchronously in Phase 4
- Query handlers reuse the same write repository — no separate read repos until performance requires it
- Cross-context event handlers use **ID-only references** — they never reach into another context's aggregate to read state