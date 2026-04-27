# Domain entities overview

## Tech stack clarification
This plugin runs on **WordPress only** — no WooCommerce. WordPress provides:
- Routing (REST API via `register_rest_route`)
- Admin UI (wp-admin pages, meta boxes)
- Authentication (WP users, roles, nonces)
- Database access (wpdb, custom tables)

All domain entities use **UUID** identities — never WordPress post IDs or user IDs as domain identity.

---

## Bounded contexts and their aggregate roots

| Context  | Aggregate root     | Key entities                    | Key value objects                                     |
|----------|--------------------|---------------------------------|-------------------------------------------------------|
| Vendor   | `Vendor`           | —                               | `VendorId`, `VendorStatus`, `CommissionRate`          |
| Catalog  | `Product`          | —                               | `ProductId`, `ProductOwnership`, `Price`              |
| Cart     | `Cart`             | `CartItem`                      | `CartId`, `Quantity`                                  |
| Order    | `MarketplaceOrder` | `VendorSubOrder`, `OrderLine`   | `OrderId`, `OrderStatus`, `Commission`, `Money`       |
| Payment  | `Payout`           | —                               | `PayoutId`, `PayoutStatus`, `Money`, `PaymentMethod`  |
| Shipping | `Shipment`         | —                               | `ShipmentId`, `TrackingNumber`, `DeliveryAddress`     |
| Review   | `Review`           | —                               | `ReviewId`, `Rating`, `ReviewStatus`                  |

---

## Cross-context references (by ID only — never by object)

- `Product` holds `VendorId` — who owns this product
- `Cart` holds `ProductId` per item — what the buyer wants to buy
- `VendorSubOrder` holds `VendorId` — whose sub-order it is
- `OrderLine` holds `ProductId` — price captured at order time
- `Payout` holds `VendorId` + `SubOrderId`
- `Shipment` holds `VendorId` + `SubOrderId`
- `Review` holds `VendorId` + `ProductId`

---

## Cart: domain entity, not infrastructure state

Cart lives in the domain because it has **invariants and behaviour**:
- Cannot add a product from a suspended vendor
- Cannot add a product with quantity < 1
- Cannot add a product that is deactivated
- `checkout()` transitions to `MarketplaceOrder` — this is domain logic

Where the cart is *stored* (PHP session, database, Redis) is an infrastructure decision made in `CartRepository`. The domain does not care.

---

## What WordPress provides (outside the domain)

| Concern              | Handled by                        |
|----------------------|-----------------------------------|
| Authentication       | WordPress user system             |
| Buyer identity       | WP user ID (referenced as string) |
| Admin UI rendering   | WP admin pages (Presentation layer) |
| REST routing         | `register_rest_route` (Presentation layer) |
| Cron jobs            | `wp_schedule_event` (Infrastructure layer) |
| Raw DB access        | `wpdb` (Infrastructure layer only) |

WordPress **never enters the Domain or Application layers**.