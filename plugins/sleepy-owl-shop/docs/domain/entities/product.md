# Entity: Product

**Context:** Catalog  
**Type:** Aggregate root  
**File:** `src/Catalog/Domain/Model/Product.php`

## Description
Represents a product listing owned by a specific vendor. Tracks ownership and pricing. Commission is fixed per vendor — products do not override it. Product data is persisted via `ProductRepository` in the Infrastructure layer. The domain aggregate owns the rules — storage is an infrastructure concern.

## Identity
`ProductId` — UUID, immutable after creation.

## State (value objects)

| Field          | Type               | Notes                                           |
|----------------|--------------------|-------------------------------------------------|
| `id`           | `ProductId`        | Immutable                                       |
| `ownership`    | `ProductOwnership` | Contains `VendorId` — who owns this product     |
| `price`        | `Money`            | Reuses `Shared\Domain\Money` — amount + currency |
| `status`       | `ProductStatus`    | `draft`, `active`, `deactivated`                |

## Invariants
- A product must always have an owner (`VendorId`)
- Price must be positive
- A deactivated product cannot receive new orders

## Behaviour (methods)

| Method                            | Description                                           |
|-----------------------------------|-------------------------------------------------------|
| `assignToVendor(VendorId)`        | Sets ownership, raises `ProductOwnershipAssigned`     |
| `activate()`                      | Makes product visible, raises `ProductActivated`      |
| `deactivate()`                    | Hides product, raises `ProductDeactivated`            |
| `updatePrice(Money)`              | Updates price, raises `ProductPriceUpdated`           |

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