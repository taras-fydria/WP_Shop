# Implementation status

| Context | Artifact | Code | Tests | Notes |
|---------|----------|------|-------|-------|
| **Shared** | `DomainEvent` interface | ✅ | — | |
| **Shared** | `AbstractDomainEvent` base class | ✅ | — | Auto-sets `occurredAt` |
| **Shared** | `AggregateRoot` base class | ✅ | — | `raiseEvent()` + `releaseEvents()` |
| **Shared** | `Money` VO | ✅ | ✅ | |
| **Shared** | `DeliveryAddress` VO | ✅ | ✅ | |
| **Shared** | `CommissionRate` VO | ✅ | ✅ | |
| **Order** | [`MarketplaceOrder` aggregate](entities/order.md#marketplaceorder-aggregate-root) | ✅ | ✅ | |
| **Order** | [`VendorSubOrder` entity](entities/order.md#vendorsuborder-entity-inside-marketplaceorder) | ✅ | ✅ | |
| **Order** | [`OrderLine` VO](entities/order.md#orderline-value-object-inside-vendorsuborder) | ✅ | ✅ | |
| **Order** | `OrderId`, `SubOrderId`, `OrderStatus`, `SubOrderStatus` VOs | ✅ | — | |
| **Order** | `OrderSplitter` domain service | ✅ | ✅ | |
| **Order** | `CommissionEngine` domain service | ✅ | ✅ | |
| **Order** | Domain events (OrderPlaced, OrderPaid, OrderSplit, OrderCompleted, OrderCancelled, SubOrder*) | ✅ | — | |
| **Cart** | [`Cart` aggregate](entities/cart.md) | ✅ | ✅ | |
| **Cart** | [`CartItem` entity](entities/cart.md#cartitem-entity-inside-cart) | ✅ | ✅ | |
| **Cart** | `CartId`, `Quantity` VOs | ✅ | ✅ | |
| **Cart** | Domain events (ItemAdded, ItemRemoved, QuantityUpdated, CartCleared, CartCheckedOut) | ✅ | — | |
| **Catalog** | `ProductId` VO | ✅ | — | |
| **Catalog** | [`Product` aggregate](entities/product.md) | ✅ | ✅ | |
| **Catalog** | `ProductOwnership`, `ProductStatus` VOs | ✅ | — | |
| **Catalog** | Domain events (ProductCreated, ProductOwnershipAssigned, ProductActivated, ProductDeactivated, ProductPriceUpdated) | ✅ | — | |
| **Vendor** | `VendorId` VO | ✅ | — | |
| **Vendor** | [`Vendor` aggregate](entities/vendor.md) | ✅ | ✅ | |
| **Vendor** | `VendorStatus`, `PaymentProfile` VOs | ✅ | — | |
| **Vendor** | Domain events (VendorRegistered, VendorApproved, VendorSuspended, VendorReinstated, CommissionRateUpdated) | ✅ | — | |
| **Payment** | [`Payout` aggregate](entities/payout_shipment.md#entity-payout) | ✅ | ✅ | |
| **Payment** | `PayoutId`, `PayoutStatus`, `PaymentMethod` VOs | ✅ | ❌ | |
| **Payment** | `PaymentGatewayInterface` port | ✅ | — | |
| **Payment** | Domain events (PayoutInitiated, PayoutCompleted, PayoutFailed) | ✅ | — | |
| **Shipping** | [`Shipment` aggregate](entities/payout_shipment.md#entity-shipment) | ✅ | ✅ | |
| **Shipping** | `ShipmentId`, `TrackingNumber`, `ShipmentStatus` VOs | ✅ | ❌ | |
| **Shipping** | `ShippingProviderInterface` port | ✅ | — | |
| **Shipping** | Domain events (ShipmentCreated, ShipmentDispatched, TrackingUpdated, ShipmentDelivered) | ✅ | — | |
| **Review** | [`Review` aggregate](entities/review.md) | ✅ | ✅ | |
| **Review** | `ReviewId`, `Rating`, `ReviewStatus` VOs | ✅ | ❌ | |
| **Review** | Domain events (ReviewSubmitted, ReviewApproved, ReviewRejected) | ✅ | — | |
| **Vendor** | [`VendorRepositoryInterface`](repositories.md#vendorrepositoryinterface) | ❌ | — | Domain port |
| **Catalog** | [`ProductRepositoryInterface`](repositories.md#productrepositoryinterface) | ❌ | — | Domain port |
| **Cart** | [`CartRepositoryInterface`](repositories.md#cartrepositoryinterface) | ❌ | — | Domain port |
| **Order** | [`OrderRepositoryInterface`](repositories.md#orderrepositoryinterface) | ❌ | — | Domain port |
| **Payment** | [`PayoutRepositoryInterface`](repositories.md#payoutrepositoryinterface) | ❌ | — | Domain port |
| **Shipping** | [`ShipmentRepositoryInterface`](repositories.md#shipmentrepositoryinterface) | ❌ | — | Domain port |
| **Review** | [`ReviewRepositoryInterface`](repositories.md#reviewrepositoryinterface) | ❌ | — | Domain port |
