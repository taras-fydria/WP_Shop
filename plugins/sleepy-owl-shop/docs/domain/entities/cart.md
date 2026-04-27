# Entity: Cart

**Context:** Cart  
**Type:** Aggregate root  
**File:** `src/Cart/Domain/Model/Cart.php`

## Description
Represents the buyer's current selection before checkout. Has domain invariants — it is not a passive data bag. Where it is physically stored (session, DB, cache) is decided by `CartRepository` in Infrastructure.

## Identity
`CartId` — UUID. Tied to a buyer session or user reference, but the domain does not know about sessions.

## State

| Field       | Type          | Notes                                        |
|-------------|---------------|----------------------------------------------|
| `id`        | `CartId`      | Immutable                                    |
| `buyerRef`  | `string`      | WP user ID or anonymous session token        |
| `items`     | `CartItem[]`  | Current items in the cart                    |
| `updatedAt` | `DateTimeImmutable` |                                        |

## CartItem (entity inside Cart)

| Field       | Type        | Notes                                              |
|-------------|-------------|----------------------------------------------------|
| `productId` | `ProductId` | Reference to Catalog context (ID only)             |
| `vendorId`  | `VendorId`  | Denormalised for invariant checking (ID only)      |
| `quantity`  | `Quantity`  | Value object, must be >= 1                         |
| `unitPrice` | `Money`     | Captured at time of adding to cart                 |

## Invariants
- Quantity must be >= 1
- Cannot add a product that is deactivated
- Cannot add a product owned by a suspended vendor
- Cannot add the same product twice — instead update quantity
- Cart must not be empty at checkout

## Behaviour

| Method                          | Description                                           |
|---------------------------------|-------------------------------------------------------|
| `addItem(ProductId, VendorId, Money, Quantity)` | Adds or merges item, raises `ItemAddedToCart` |
| `removeItem(ProductId)`         | Removes item, raises `ItemRemovedFromCart`            |
| `updateQuantity(ProductId, Quantity)` | Updates quantity, raises `CartItemQuantityUpdated` |
| `clear()`                       | Empties cart, raises `CartCleared`                   |
| `checkout()`                    | Validates not empty, raises `CartCheckedOut` — consumed by Order context to create `MarketplaceOrder` |

## Domain events raised
`ItemAddedToCart`, `ItemRemovedFromCart`, `CartItemQuantityUpdated`, `CartCleared`, `CartCheckedOut`

## Infrastructure note
`CartRepository` decides storage strategy. For anonymous buyers: PHP session or transient. For logged-in buyers: custom DB table. The domain `Cart` object is the same in both cases.