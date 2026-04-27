# Entity: Payout

**Context:** Payment  
**Type:** Aggregate root  
**File:** `src/Payment/Domain/Model/Payout.php`

## Description
Represents a single payout to a vendor after their sub-order is completed. Tracks the lifecycle of the money transfer through Stripe Connect or LiqPay.

## Identity
`PayoutId` — UUID, independent of payment gateway IDs.

## State

| Field           | Type            | Notes                                                  |
|-----------------|-----------------|--------------------------------------------------------|
| `id`            | `PayoutId`      | Immutable                                              |
| `vendorId`      | `VendorId`      | Who receives the payout (ID only)                      |
| `subOrderId`    | `SubOrderId`    | Which sub-order triggered this payout (ID only)        |
| `amount`        | `Money`         | Net amount after commission deduction                  |
| `status`        | `PayoutStatus`  | `pending`, `initiated`, `completed`, `failed`          |
| `method`        | `PaymentMethod` | `stripe_connect` or `liqpay`                           |
| `gatewayRef`    | `string\|null`  | External transfer ID from Stripe / LiqPay              |
| `initiatedAt`   | `DateTimeImmutable\|null` |                                              |
| `completedAt`   | `DateTimeImmutable\|null` |                                              |

## Invariants
- Amount must be positive
- Cannot transition from `completed` or `failed` to any other status
- `gatewayRef` is set only after gateway confirms initiation

## Behaviour

| Method            | Description                                            |
|-------------------|--------------------------------------------------------|
| `initiate()`      | Calls gateway, sets `gatewayRef`, raises `PayoutInitiated` |
| `markCompleted()` | Gateway webhook confirms success, raises `PayoutCompleted` |
| `markFailed(reason)` | Gateway webhook reports failure, raises `PayoutFailed` |

## Domain events raised
`PayoutInitiated`, `PayoutCompleted`, `PayoutFailed`

## Port (interface)
`PaymentGatewayInterface` — implemented by `StripeConnectGateway` and `LiqPayGateway` in Infrastructure.

---

# Entity: Shipment

**Context:** Shipping  
**Type:** Aggregate root  
**File:** `src/Shipping/Domain/Model/Shipment.php`

## Description
Represents a physical shipment created by a vendor for their sub-order. Tracks the Nova Poshta (or other provider) tracking number and delivery status.

## Identity
`ShipmentId` — UUID.

## State

| Field          | Type              | Notes                                               |
|----------------|-------------------|-----------------------------------------------------|
| `id`           | `ShipmentId`      | Immutable                                           |
| `vendorId`     | `VendorId`        | Who created the shipment (ID only)                  |
| `subOrderId`   | `SubOrderId`      | Which sub-order this shipment belongs to (ID only)  |
| `address`      | `DeliveryAddress` | Buyer's delivery address, captured at creation      |
| `tracking`     | `TrackingNumber\|null` | Set when vendor dispatches                     |
| `status`       | `ShipmentStatus`  | `created`, `dispatched`, `in_transit`, `delivered`  |
| `provider`     | `string`          | e.g. `nova_poshta`                                  |
| `createdAt`    | `DateTimeImmutable` |                                                   |

## Invariants
- Cannot be dispatched without a tracking number
- Cannot update tracking on a delivered shipment
- Delivery address is immutable after creation

## Behaviour

| Method                       | Description                                         |
|------------------------------|-----------------------------------------------------|
| `create()`                   | Raises `ShipmentCreated`                            |
| `dispatch(TrackingNumber)`   | Sets tracking, raises `ShipmentDispatched`          |
| `updateStatus(ShipmentStatus)` | Polling job updates, raises `TrackingUpdated`     |
| `markDelivered()`            | Raises `ShipmentDelivered`                          |

## Domain events raised
`ShipmentCreated`, `ShipmentDispatched`, `TrackingUpdated`, `ShipmentDelivered`

## Port (interface)
`ShippingProviderInterface` — implemented by `NovaPoshtaGateway` in Infrastructure.

## Infrastructure note
`TrackingPollingJob` (WP-Cron) periodically calls `NovaPoshtaGateway` and updates shipment status via `UpdateTracking` command.