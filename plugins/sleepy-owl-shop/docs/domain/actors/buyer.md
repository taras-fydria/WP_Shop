# Actor: Buyer

## Description
An end user who browses the marketplace and purchases products from one or more vendors in a single order.

## Goals
- Find products quickly and compare offers across vendors
- Complete checkout with minimal friction
- Receive ordered goods on time and track delivery
- Have a way to resolve issues with orders

## Scenarios

### 1. Browse catalog
Buyer visits the marketplace, filters products by category, price, or vendor, and views individual product pages.

### 2. Add to cart
Buyer adds products from one or more vendors to the cart. The system keeps track of which vendor each item belongs to.

### 3. Place order
Buyer proceeds to checkout, selects a delivery address and payment method, and confirms the order. The system splits the order into vendor sub-orders internally.

### 4. Pay for order
Buyer completes payment via Stripe (global) or LiqPay (UA market). Funds are held and later distributed to vendors.

### 5. Track delivery
Buyer receives shipment tracking information per vendor sub-order and monitors delivery status via Nova Poshta or other providers.

### 6. Leave a review
After receiving the goods, Buyer can rate the product and the vendor.

### 7. Request support
Buyer contacts support regarding a dispute, damaged goods, or non-delivery.

## Constraints
- Buyer does not know about the internal order split — they see one order, not sub-orders
- Buyer interacts only with the Presentation layer (storefront, REST API)
- Buyer cannot access vendor dashboards or admin panels

## Related Entities
- `Order` — created when buyer places a purchase
- `Cart` — temporary state before order placement
- `Review` — created by buyer after delivery