# Repository Interfaces

**Layer:** Domain (ports)  
**Files:** `src/{Context}/Domain/Repository/*RepositoryInterface.php`

## Description

Repository interfaces are domain ports — contracts defined inside the domain layer, implemented in the Infrastructure layer. The domain never knows how data is persisted; aggregates are loaded and saved through these interfaces. Implementations (`$wpdb`-based) live in `src/{Context}/Infrastructure/Repository/`.

Each repository has explicit `add()` (INSERT) and `update()` methods. The use case always knows the intent — no upsert guessing in infrastructure.

---

## VendorRepositoryInterface

**Context:** Vendor  
**File:** `src/Vendor/Domain/Repository/VendorRepositoryInterface.php`

| Method       | Signature                     | Description                    |
|--------------|-------------------------------|--------------------------------|
| `findById`   | `findById(VendorId): ?Vendor` | Returns `null` if not found    |
| `add`        | `add(Vendor): void`           | INSERT — fails if exists       |
| `update`     | `update(Vendor): void`        | UPDATE — fails if not found    |
| `delete`     | `delete(VendorId): void`      | DELETE — hard remove by ID     |

---

## ProductRepositoryInterface

**Context:** Catalog  
**File:** `src/Catalog/Domain/Repository/ProductRepositoryInterface.php`

| Method     | Signature                        | Description                 |
|------------|----------------------------------|-----------------------------|
| `findById` | `findById(ProductId): ?Product`  | Returns `null` if not found |
| `add`      | `add(Product): void`             | INSERT — fails if exists    |
| `update`   | `update(Product): void`          | UPDATE — fails if not found |
| `delete`   | `delete(ProductId): void`        | DELETE — hard remove by ID  |

---

## CartRepositoryInterface

**Context:** Cart  
**File:** `src/Cart/Domain/Repository/CartRepositoryInterface.php`

| Method            | Signature                          | Description                              |
|-------------------|------------------------------------|------------------------------------------|
| `findByBuyerRef`  | `findByBuyerRef(string): ?Cart`    | Lookup by WP user ID or session token    |
| `add`             | `add(Cart): void`                  | INSERT — fails if exists                 |
| `update`          | `update(Cart): void`               | UPDATE — fails if not found              |
| `delete`          | `delete(CartId): void`             | Remove cart after checkout               |

**Note:** `buyerRef` is a string holding either a WP user ID or an anonymous session token. The repository implementation decides the storage mechanism (DB, transient, session).

---

## OrderRepositoryInterface

**Context:** Order  
**File:** `src/Order/Domain/Repository/OrderRepositoryInterface.php`

| Method     | Signature                                  | Description                                                                  |
|------------|--------------------------------------------|------------------------------------------------------------------------------|
| `findById` | `findById(OrderId): ?MarketplaceOrder`     | Returns `null` if not found                                                  |
| `add`      | `add(MarketplaceOrder): void`              | INSERT aggregate — includes sub-orders and order lines                       |
| `update`   | `update(MarketplaceOrder): void`           | UPDATE aggregate — persists sub-order and order line changes                 |
| `delete`   | `delete(OrderId): void`                    | DELETE — hard remove by ID                                                   |

**Note:** `MarketplaceOrder` is the aggregate root and owns `VendorSubOrder[]`. The repository always reconstitutes and persists the full aggregate — sub-orders are not fetched or saved independently.

---

## PayoutRepositoryInterface

**Context:** Payment  
**File:** `src/Payment/Domain/Repository/PayoutRepositoryInterface.php`

| Method              | Signature                            | Description                              |
|---------------------|--------------------------------------|------------------------------------------|
| `findById`          | `findById(PayoutId): ?Payout`           | Returns `null` if not found              |
| `findBySubOrderId`  | `findBySubOrderId(SubOrderId): ?Payout` | Lookup payout for a specific sub-order   |
| `add`               | `add(Payout): void`                     | INSERT — fails if exists                 |
| `update`            | `update(Payout): void`                  | UPDATE — fails if not found              |
| `delete`            | `delete(PayoutId): void`                | DELETE — hard remove by ID               |

---

## ShipmentRepositoryInterface

**Context:** Shipping  
**File:** `src/Shipping/Domain/Repository/ShipmentRepositoryInterface.php`

| Method              | Signature                               | Description                               |
|---------------------|-----------------------------------------|-------------------------------------------|
| `findById`          | `findById(ShipmentId): ?Shipment`         | Returns `null` if not found               |
| `findBySubOrderId`  | `findBySubOrderId(SubOrderId): ?Shipment` | Lookup shipment for a specific sub-order  |
| `add`               | `add(Shipment): void`                     | INSERT — fails if exists                  |
| `update`            | `update(Shipment): void`                  | UPDATE — fails if not found               |
| `delete`            | `delete(ShipmentId): void`                | DELETE — hard remove by ID                |

**Note:** Used by `TrackingPollingJob` (WP-Cron) — polling queries all active shipments, then saves updated status via this interface.

---

## ReviewRepositoryInterface

**Context:** Review  
**File:** `src/Review/Domain/Repository/ReviewRepositoryInterface.php`

| Method     | Signature                      | Description                 |
|------------|--------------------------------|-----------------------------|
| `findById` | `findById(ReviewId): ?Review`  | Returns `null` if not found |
| `add`      | `add(Review): void`            | INSERT — fails if exists    |
| `update`   | `update(Review): void`         | UPDATE — fails if not found |
| `delete`   | `delete(ReviewId): void`       | DELETE — hard remove by ID  |

---

## Design notes

- All IDs are UUIDs — no WP post/user IDs inside the domain layer
- Repositories return `null` on not-found — pure data ports with no opinion on whether absence is an error
- **Not-found enforced in the use case:** each handler checks the result and throws a context-specific `NotFoundException` when the entity must exist
- Each context owns its own exception: `VendorNotFoundException`, `OrderNotFoundException`, etc. — extending the context's domain exception
- Implementations live in `src/{Context}/Infrastructure/Repository/` (Phase 4)
- Read-side queries (listing, filtering, pagination) are handled by separate Query objects, not repositories — repositories are aggregate-focused (load one, save one)

### Not-found pattern

```php
// Repository — pure port, no opinions
findById(VendorId $id): ?Vendor;

// Use case handler — owns the "must exist" rule
$vendor = $this->vendorRepository->findById($command->vendorId);
if ($vendor === null) {
    throw new VendorNotFoundException($command->vendorId);
}
```