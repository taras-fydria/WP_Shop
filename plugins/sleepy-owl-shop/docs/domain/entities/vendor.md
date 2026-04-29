# Entity: Vendor

**Context:** Vendor  
**Type:** Aggregate root  
**File:** `src/Vendor/Domain/Model/Vendor.php`

## Description
Represents a seller registered on the marketplace. Owns its approval lifecycle and holds the commission rate assigned by the Administrator.

## Identity
`VendorId` — a UUID value object. Never use raw `int` or WordPress user ID as identity inside the domain.

## State (value objects)

| Field            | Type             | Notes                                              |
|------------------|------------------|----------------------------------------------------|
| `id`             | `VendorId`       | Immutable after creation                           |
| `status`         | `VendorStatus`   | `pending`, `approved`, `suspended`                 |
| `commissionRate` | `CommissionRate` | Set by Administrator. Applied to all sub-orders belonging to this vendor. Single source of truth for commission. |
| `businessName`   | `string`         | Display name on the marketplace                    |
| `paymentProfile` | `PaymentProfile` | Stripe account ID or LiqPay credentials (VO)       |
| `createdAt`      | `DateTimeImmutable` |                                                 |

## Invariants (rules the aggregate enforces)
- A vendor cannot be approved if already approved
- A vendor cannot be suspended if already suspended
- Commission rate must be between 0% and 100%
- Payment profile is required before vendor can receive payouts

## Behaviour (methods)

| Method               | Description                                              |
|----------------------|----------------------------------------------------------|
| `register()`         | Creates vendor in `pending` status, raises `VendorRegistered` |
| `approve()`          | Transitions to `approved`, raises `VendorApproved`       |
| `suspend(reason)`    | Transitions to `suspended`, raises `VendorSuspended`     |
| `reinstate()`        | Transitions back to `approved`, raises `VendorReinstated`|
| `updateCommissionRate(CommissionRate)` | Admin sets rate, raises `CommissionRateUpdated` |

## Domain events raised

- `VendorRegistered`
- `VendorApproved`
- `VendorSuspended`
- `VendorReinstated`
- `CommissionRateUpdated`

## Cross-context references
Referenced by `VendorSubOrder` and `Review` via `VendorId` only — never as an object.