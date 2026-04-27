# Actor: Administrator

## Description
The marketplace owner or technical operator. Configures the platform rules, manages payment and shipping integrations, controls roles, and monitors the overall financial health of the marketplace.

## Goals
- Keep the platform running reliably
- Ensure commission and payout rules are correct and enforced
- Monitor revenue and operational metrics
- Control who has what level of access

## Scenarios

### 1. Configure commission rules
Administrator sets global and per-vendor commission rates. These rates are applied automatically by `CommissionEngine` when orders are split.

### 2. Manage payment gateways
Administrator configures Stripe Connect credentials (API keys, webhook secrets) and LiqPay credentials. Chooses which gateways are active for which regions.

### 3. Configure shipping providers
Administrator sets up Nova Poshta API credentials and configures shipping zones and rate rules per vendor or region.

### 4. Manage roles and access
Administrator assigns the Manager role to staff members. Controls what each role can see and do within the WordPress admin.

### 5. View financial reports
Administrator reviews platform-level reports: total revenue, commissions earned, pending payouts, failed payouts, and vendor performance summaries.

### 6. Trigger manual payout
In exceptional cases (payout failure, vendor dispute resolution), Administrator can manually initiate or cancel a payout.

### 7. Manage plugin settings
Administrator configures global plugin options: marketplace name, currency, supported regions, notification templates.

## Constraints
- Administrator has full access to all data across all vendors and orders
- Administrator changes to commission rates do not retroactively affect completed orders
- Credentials for payment gateways are stored encrypted and never exposed in the UI

## Related Entities
- `CommissionRate` — value object configured by Administrator
- `Payout` — Administrator can view and manually trigger payouts
- `PaymentGateway` configuration — managed by Administrator
- `ShippingProvider` configuration — managed by Administrator