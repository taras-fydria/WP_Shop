# Actor: Vendor (Seller)

## Description
A registered seller who lists products on the marketplace, fulfills orders assigned to them, and receives payouts for completed sales.

## Goals
- List and manage products with their own pricing
- Receive and process incoming orders efficiently
- Arrange shipment and provide tracking information
- Get paid reliably and transparently

## Scenarios

### 1. Register as vendor
Vendor submits a registration form with business details. Account is created with `pending` status and awaits approval from a Manager.

### 2. Await approval
Vendor waits for Manager review. They cannot list products or receive orders until their status becomes `approved`.

### 3. Manage products
Vendor creates, edits, and deactivates their product listings via the vendor dashboard. Each product is owned by this vendor.

### 4. Receive sub-order
When a Buyer places an order containing this vendor's products, the system creates a `VendorSubOrder` and notifies the vendor.

### 5. Process sub-order
Vendor confirms the sub-order, prepares the goods, and marks it as ready for shipment.

### 6. Arrange shipment
Vendor creates a shipment via Nova Poshta (or another provider), enters the tracking number into the system.

### 7. Receive payout
After the sub-order is completed, the system initiates a payout to the vendor's Stripe Connect or LiqPay account, minus the marketplace commission.

### 8. View sales analytics
Vendor views their dashboard: revenue, order history, payout history, and product performance.

### 9. Get suspended
If vendor violates platform rules, a Manager suspends their account. Vendor loses ability to receive new orders until reinstated.

## Constraints
- Vendor sees only their own products, orders, and payouts — not other vendors' data
- Vendor cannot approve themselves or change their own status
- Commission rate is set by the Administrator and applied automatically

## Related Entities
- `Vendor` — aggregate root representing this actor in the domain
- `VendorSubOrder` — the portion of a buyer order assigned to this vendor
- `Product` — owned by this vendor
- `Shipment` — created by this vendor per sub-order
- `Payout` — issued to this vendor after order completion