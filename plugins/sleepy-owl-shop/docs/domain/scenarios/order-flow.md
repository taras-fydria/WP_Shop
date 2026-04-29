# Main scenario: from cart to payout

## Overview

This is the core business flow of the marketplace. A buyer adds products from multiple vendors, places one order, and the system handles everything else — splitting, shipping, and paying each vendor independently.

The buyer sees one order. Internally the system creates as many sub-orders as there are vendors involved.

---

## Step 1 — Buyer adds items to cart

**Actor:** Buyer  
**Entities:** `Cart`, `CartItem`, `ProductId`, `VendorId`  
**Events raised:** `ItemAddedToCart`

Buyer browses the catalog and adds products. On each `addItem()` call the `Cart` aggregate validates:
- Product must be active
- Vendor who owns the product must be approved
- Quantity must be >= 1
- If the same product is added twice — quantity is merged, no duplicate line

Cart is stored in PHP session. No database table. If the session expires, the cart is lost — this is acceptable by design.

---

## Step 2 — Buyer proceeds to checkout

**Actor:** Buyer  
**Entities:** `Cart`, `DeliveryAddress`  
**Events raised:** `CartCheckedOut`

Buyer confirms cart contents and provides a delivery address. Cart calls `checkout()` which validates the cart is not empty, then raises `CartCheckedOut`. The Order context listens to this event and takes over.

Cart is cleared after `CartCheckedOut` is dispatched.

---

## Step 3 — Order is created

**Actor:** System  
**Entities:** `MarketplaceOrder`, `OrderId`, `Money`  
**Events raised:** `OrderPlaced`, `OrderPaid`

The Order context handles `CartCheckedOut` and creates a `MarketplaceOrder` in `pending` status. Payment is immediately stubbed via `FakePaymentGateway` which always returns success. `OrderPaid` is raised right after `OrderPlaced`.

Real payment integration (Stripe Connect, LiqPay) replaces the fake gateway later without touching domain logic — only the Infrastructure adapter changes.

---

## Step 4 — Order is split into sub-orders

**Actor:** System  
**Entities:** `MarketplaceOrder`, `VendorSubOrder`, `OrderLine`, `CommissionRate`  
**Events raised:** `OrderSplit`, `SubOrderCreated`

This is the most complex step and the core domain logic of the system.

`OrderSplitter` domain service groups `OrderLine` items by `VendorId`. For each vendor group it creates a `VendorSubOrder` with its own status lifecycle. `CommissionEngine` reads each vendor's `CommissionRate` (owned by the Vendor aggregate) and snapshots it into the `VendorSubOrder` at split time.

The snapshotted rate is immutable — if the Administrator later changes a vendor's commission, existing orders are not affected.

Invariants enforced:
- Each sub-order must have at least one line
- An order cannot be split more than once

The buyer is never aware of the split. They see one `MarketplaceOrder` with one total.

---

## Step 5 — Vendor confirms sub-order

**Actor:** Vendor  
**Entities:** `VendorSubOrder`, `SubOrderStatus`  
**Events raised:** `SubOrderConfirmed`

Each vendor sees their own `VendorSubOrder` in the vendor dashboard via REST API. They confirm it — signalling they have the goods ready. A vendor cannot see other vendors' sub-orders.

Invariants enforced:
- Cannot confirm an already confirmed sub-order
- Cannot confirm a cancelled sub-order

---

## Step 6 — Vendor creates shipment

**Actor:** Vendor  
**Entities:** `Shipment`, `TrackingNumber`, `DeliveryAddress`  
**Events raised:** `ShipmentCreated`, `SubOrderDispatched`

Vendor enters a tracking number from Nova Poshta (or another provider). The Shipping context creates a `Shipment` aggregate and raises `ShipmentCreated`. An Application handler listens to `ShipmentCreated` and calls `VendorSubOrder.dispatch()` — the sub-order transitions to `dispatched`.

`TrackingNumber` belongs to the Shipping context (`Shipment` aggregate). The Order context does not store or validate it.

`DeliveryAddress` is captured from the original order at shipment creation time — immutable afterwards.

Invariants enforced:
- `TrackingNumber` is required before `Shipment` can be dispatched (Shipping context invariant)
- Cannot dispatch a `VendorSubOrder` without confirming it first (Order context invariant)

---

## Step 7 — Delivery tracked automatically

**Actor:** System (WP-Cron)  
**Entities:** `Shipment`, `ShipmentStatus`  
**Events raised:** `TrackingUpdated`, `ShipmentDelivered`

`TrackingPollingJob` runs on WP-Cron schedule. It calls `ShippingProviderInterface` (implemented by `NovaPoshtaGateway`) for each active shipment and updates `ShipmentStatus`. Buyer can query current status via REST API.

Status progression: `dispatched` → `in_transit` → `delivered`

Invariants enforced:
- Cannot update status of an already delivered shipment

---

## Step 8 — Sub-order is completed

**Actor:** System  
**Entities:** `VendorSubOrder`, `MarketplaceOrder`  
**Events raised:** `SubOrderCompleted`, `OrderCompleted`

When a shipment reaches `delivered` status, the corresponding `VendorSubOrder` is marked `completed`. When all sub-orders of a `MarketplaceOrder` are completed, the order itself transitions to `completed` and raises `OrderCompleted`.

Invariants enforced:
- `MarketplaceOrder` can only complete when every `VendorSubOrder` is completed

---

## Step 9 — Vendor receives payout

**Actor:** System  
**Entities:** `Payout`, `Money`, `PayoutStatus`, `PaymentMethod`  
**Events raised:** `PayoutInitiated`, `PayoutCompleted`

Payment context listens to `SubOrderCompleted`. Creates a `Payout` aggregate for the vendor. Amount equals the sub-order subtotal minus commission.

Currently `FakePaymentGateway` marks payout as completed immediately. Future adapters (`StripeConnectGateway`, `LiqPayGateway`) will handle real transfers via `PaymentGatewayInterface` without any domain changes.

Invariants enforced:
- Payout amount must be positive
- Cannot transition from `completed` or `failed` to any other status

---

## Step 10 — Buyer leaves a review

**Actor:** Buyer  
**Entities:** `Review`, `Rating`, `ReviewStatus`  
**Events raised:** `ReviewSubmitted`, `ReviewApproved`

After delivery, buyer submits a `Review` referencing both `ProductId` and `VendorId`. Review enters `pending` status. A Manager moderates it — approving or rejecting via the admin panel. Only approved reviews become visible on the storefront.

Invariants enforced:
- Rating must be between 1 and 5
- One review per product per order
- Comment cannot be empty
- A rejected review cannot be approved without re-submission

---

## Event flow summary

```
CartCheckedOut
  → OrderPlaced
  → OrderPaid
  → OrderSplit
    → SubOrderCreated (×N vendors)
      → SubOrderConfirmed (×N)
        → ShipmentCreated (×N)
          → ShipmentDispatched (×N)
            → TrackingUpdated (×N, polling)
              → ShipmentDelivered (×N)
                → SubOrderCompleted (×N)
                  → PayoutInitiated (×N)
                  → PayoutCompleted (×N)
  → OrderCompleted (when all sub-orders done)
```

---

## Where to start writing code

Split into sub-orders (step 4) is the highest-value starting point:
- It exercises the most domain logic
- It requires `CommissionEngine`, `OrderSplitter`, `VendorSubOrder`, `OrderLine`, `Commission`
- It has clear invariants — easy to write TDD tests first
- Everything else (payment, shipping) depends on it being correct

Recommended TDD order:
1. `Commission` value object — immutable, validates range
2. `OrderLine` value object — captures price at order time
3. `VendorSubOrder` entity — lifecycle, invariants
4. `MarketplaceOrder` aggregate — holds sub-orders, delegates split
5. `OrderSplitter` domain service — groups lines by vendor
6. `CommissionEngine` domain service — calculates commission per sub-order