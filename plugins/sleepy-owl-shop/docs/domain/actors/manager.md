# Actor: Manager (Moderator)

## Description
A platform staff member responsible for the quality and safety of the marketplace. Approves vendors, moderates content, and resolves disputes between buyers and vendors.

## Goals
- Ensure only legitimate vendors operate on the platform
- Keep product listings accurate and policy-compliant
- Resolve conflicts fairly and quickly
- Maintain platform reputation and buyer trust

## Scenarios

### 1. Review vendor application
Manager receives a notification about a new vendor registration. They review the submitted details and either approve or reject the application, providing a reason if rejected.

### 2. Suspend vendor
If a vendor violates platform rules (fraud, policy breach, repeated complaints), Manager suspends their account. Active sub-orders may need manual handling.

### 3. Reinstate vendor
After a vendor resolves the issue, Manager lifts the suspension and restores their active status.

### 4. Moderate product listings
Manager reviews flagged products for policy violations (prohibited items, misleading descriptions, incorrect categories) and removes or requests corrections.

### 5. Handle buyer complaint
Buyer raises a dispute against a vendor. Manager investigates both sides, reviews order and shipment data, and makes a resolution decision (refund, replacement, or dismissal).

### 6. Review negative feedback
Manager reviews reviews flagged as abusive or fake and can remove them with a logged reason.

## Constraints
- Manager cannot change commission rates or payment gateway settings — that is Administrator territory
- Manager actions are logged for audit purposes
- Manager operates within the WordPress admin panel

## Related Entities
- `Vendor` — Manager changes vendor status (`approved`, `suspended`)
- `Product` — Manager can deactivate or flag listings
- `Order` / `VendorSubOrder` — Manager can view full order details for dispute resolution
- `Review` — Manager can moderate buyer reviews