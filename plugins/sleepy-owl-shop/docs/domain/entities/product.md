# Entity: Product

**Context:** Catalog  
**Type:** Aggregate root  
**File:** `src/Catalog/Domain/Model/Product.php`

## Description
Represents a product listing owned by a specific vendor. Tracks ownership, pricing, and commission policy. The actual WooCommerce product post is the storage mechanism — this aggregate wraps the domain rules around it.

## Identity
`ProductId` — wraps the WooCommerce post ID. Immutable after creation.

## State (value objects)

| Field          | Type               | Notes                                           |
|----------------|--------------------|-------------------------------------------------|
| `id`           | `ProductId`        | Immutable                                       |
| `ownership`    | `ProductOwnership` | Contains `VendorId` — who owns this product     |
| `price`        | `Price`            | Contains amount + currency                      |
| `status`       | `ProductStatus`    | `draft`, `active`, `deactivated`                |
| `commissionPolicy` | `CommissionPolicy` | Override or inherit vendor-level rate       |

## Invariants
- A product must always have an owner (`VendorId`)
- Price must be positive
- A deactivated product cannot receive new orders
- Commission policy override, if set, must be between 0% and 100%

## Behaviour (methods)

| Method                            | Description                                           |
|-----------------------------------|-------------------------------------------------------|
| `assignToVendor(VendorId)`        | Sets ownership, raises `ProductOwnershipAssigned`     |
| `activate()`                      | Makes product visible, raises `ProductActivated`      |
| `deactivate()`                    | Hides product, raises `ProductDeactivated`            |
| `updatePrice(Price)`              | Updates price, raises `ProductPriceUpdated`           |
| `setCommissionPolicy(CommissionPolicy)` | Overrides vendor-level rate                    |

## Domain events raised

- `ProductCreated`
- `ProductOwnershipAssigned`
- `ProductActivated`
- `ProductDeactivated`
- `ProductPriceUpdated`

## Cross-context references
- References `VendorId` from the Vendor context (by ID only)
- Referenced by `OrderLine` inside the Order context via `ProductId`
- Referenced by `Review` via `ProductId`