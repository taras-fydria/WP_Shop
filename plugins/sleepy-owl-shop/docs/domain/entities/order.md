# Entities: Order context

**Context:** Order  
**Aggregate root:** `MarketplaceOrder`  
**File:** `src/Order/Domain/Model/`

---

## MarketplaceOrder (aggregate root)

### Description
Represents the buyer's full order. Contains all vendor sub-orders. Owns the lifecycle from placement to completion. The buyer sees only this entity — sub-orders are internal.

### Identity
`OrderId` — UUID, immutable after creation.

### State

| Field          | Type            | Notes                                              |
|----------------|-----------------|----------------------------------------------------|
| `id`           | `OrderId`       | Immutable                                          |
| `buyerRef`     | `string`        | WP user ID or anonymous session token — carried from `CartCheckedOut` |
| `status`       | `OrderStatus`   | `pending`, `paid`, `processing`, `completed`, `cancelled` |
| `subOrders`    | `VendorSubOrder[]` | Created during order split                      |
| `totalAmount`  | `Money`         | Full amount paid by buyer                          |
| `placedAt`     | `DateTimeImmutable` |                                                |

### Invariants
- An order cannot be split more than once
- An order cannot be completed unless all sub-orders are completed
- An order cannot be cancelled after any sub-order has been dispatched

### Behaviour

| Method                    | Description                                              |
|---------------------------|----------------------------------------------------------|
| `place(buyerRef)`         | Creates order in `pending`, raises `OrderPlaced`         |
| `markAsPaid()`            | Transitions to `paid`, raises `OrderPaid`                |
| `split(OrderSplitter)`    | Creates `VendorSubOrder` per vendor, raises `OrderSplit` |
| `complete()`              | All sub-orders done, raises `OrderCompleted`             |
| `cancel()`                | Raises `OrderCancelled`                                  |

### Domain events raised
`OrderPlaced`, `OrderPaid`, `OrderSplit`, `OrderCompleted`, `OrderCancelled`

---

## VendorSubOrder (entity inside MarketplaceOrder)

### Description
The portion of the marketplace order belonging to a single vendor. Has its own lifecycle independent of other sub-orders. Triggers payout and shipment creation.

### Identity
`SubOrderId` — UUID, immutable after creation.

### State

| Field        | Type             | Notes                                              |
|--------------|------------------|----------------------------------------------------|
| `id`         | `SubOrderId`     | Immutable                                          |
| `vendorId`   | `VendorId`       | Reference to Vendor context (ID only)              |
| `lines`      | `OrderLine[]`    | One line per product                               |
| `status`     | `SubOrderStatus` | `pending`, `confirmed`, `dispatched`, `completed`  |
| `subtotal`   | `Money`          | Sum of lines for this vendor                       |
| `commissionRate` | `CommissionRate` | Snapshot of vendor's rate at split time — immutable. Protects historical orders from future rate changes. |

### Invariants
- Must have at least one `OrderLine`
- `commissionRate` must be between 0% and 100%

### Behaviour

| Method       | Description                                            |
|--------------|--------------------------------------------------------|
| `confirm()`  | Vendor accepted, raises `SubOrderConfirmed`            |
| `dispatch()` | Raises `SubOrderDispatched`                            |
| `complete()` | Raises `SubOrderCompleted`, triggers `PayoutInitiated` |

> `dispatch()` takes no arguments. It is called by an Application handler that listens to `ShipmentCreated` from the Shipping context. `TrackingNumber` is a Shipping concern and is never stored in `VendorSubOrder`.

### Domain events raised
`SubOrderConfirmed`, `SubOrderDispatched`, `SubOrderCompleted`

---

## OrderLine (value object inside VendorSubOrder)

### Description
A single line item — one product, its quantity and price at time of order. Immutable after creation (price is captured at order time, not from current product price).

### Fields

| Field       | Type        | Notes                              |
|-------------|-------------|------------------------------------|
| `productId` | `ProductId` | Reference to Catalog (ID only)     |
| `quantity`  | `int`       | Must be >= 1                       |
| `unitPrice` | `Money`     | Captured at order time, immutable  |
| `lineTotal` | `Money`     | Computed: quantity × unitPrice     |