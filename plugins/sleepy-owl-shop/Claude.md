# CLAUDE.md

This file is read automatically by Claude Code at the start of every session.
It contains everything needed to work on this codebase without re-explaining context.

---

## What this project is

A WordPress plugin that turns a WordPress site into a multi-vendor marketplace.
Vendors register, list products, receive orders, and get paid. Buyers browse, purchase from multiple vendors in one checkout, and track deliveries.

**No WooCommerce.** WordPress is used only as a host platform — routing, admin UI, authentication, database access. All business logic is pure PHP in the domain layer.

---

## Tech stack

| Concern | Choice |
|---|---|
| Platform | WordPress (no WooCommerce) |
| Architecture | DDD — Domain / Application / Infrastructure / Presentation |
| Pattern | CQRS + Ports & Adapters |
| Testing | TDD — Pest (unit) + PHPUnit (integration) |
| DI | Manual, wired in `wp-marketplace.php` |
| Autoloading | PSR-4 via Composer |
| Payment | `FakePaymentGateway` (stub) — Stripe + LiqPay later |
| Shipping | Nova Poshta API |
| IDs | UUID everywhere — never WordPress post IDs as domain identity |

---

## Non-negotiable rules

1. **Zero WordPress/globals in Domain or Application layers.** No `get_post()`, no `$wpdb`, no `wp_*` functions. These layers are pure PHP.
2. **Value objects over primitives.** Use `VendorId`, `Money`, `TrackingNumber` — never raw `string` or `int` for domain concepts.
3. **Tests before implementation.** Red → Green → Refactor. No exceptions.
4. **Cross-context references by ID only.** `VendorSubOrder` holds `VendorId`, not a `Vendor` object.
5. **Aggregate roots own their invariants.** Business rules live in aggregates and domain services — never in command handlers or controllers.
6. **Command handlers are thin.** Load aggregate → call method → save → dispatch events. No logic.
7. **Domain events for all significant state changes.** Every aggregate method that changes state raises an event.

---

## Bounded contexts

| Context | Aggregate root | Description |
|---|---|---|
| `Vendor` | `Vendor` | Registration, approval, suspension |
| `Catalog` | `Product` | Product listings, ownership, pricing |
| `Cart` | `Cart` | Buyer's session cart, checkout trigger |
| `Order` | `MarketplaceOrder` | Order split, sub-orders, commission |
| `Payment` | `Payout` | Vendor payouts via payment gateway |
| `Shipping` | `Shipment` | Shipment creation, tracking, delivery |
| `Review` | `Review` | Buyer reviews with manager moderation |

---

## File structure

```
wp-marketplace/
├── CLAUDE.md
├── wp-marketplace.php        # Bootstrap + manual DI wiring
├── composer.json
├── phpunit.xml
├── pest.config.php
├── docs/
│   └── domain/
│       ├── actors/           # buyer.md, vendor.md, manager.md, administrator.md
│       ├── entities/         # README.md + one file per context
│       └── scenarios/        # order-flow.md
└── src/
    ├── Vendor/
    ├── Catalog/
    ├── Cart/
    ├── Order/
    ├── Payment/
    ├── Shipping/
    └── Review/
```

Each context follows this internal structure:

```
{Context}/
├── Domain/
│   ├── Model/                # Aggregate root, entities, value objects
│   ├── Event/                # Domain events
│   ├── Repository/           # Repository interfaces (ports)
│   ├── Service/              # Domain services
│   ├── Gateway/              # External gateway interfaces (Payment, Shipping only)
│   └── Exception/
├── Application/
│   ├── Command/              # Commands + handlers
│   ├── Query/                # Queries + handlers
│   └── DTO/
├── Infrastructure/
│   ├── Persistence/          # Repository implementations (wpdb)
│   ├── Gateway/              # Gateway adapters (Stripe, Nova Poshta)
│   ├── Hook/                 # WP hook listeners (Order context)
│   ├── Http/                 # Webhook endpoints (Payment context)
│   └── Scheduler/            # WP-Cron jobs (Shipping context)
└── Presentation/
    ├── RestApi/              # REST controllers
    ├── Admin/                # WP admin pages, meta boxes
    └── Frontend/             # Shortcodes, buyer-facing views
```

---

## Layer rules (quick reference)

**Domain** — pure PHP, no dependencies. Aggregates, value objects, domain events, repository interfaces, gateway interfaces.

**Application** — command handlers and query handlers. Depends only on Domain interfaces. No WP, no HTTP, no DB.

**Infrastructure** — implements Domain interfaces. Uses `wpdb`, WP hooks, WP-Cron, HTTP clients. Never imported by Domain or Application.

**Presentation** — REST controllers, admin pages. Depends on Application layer via Commands and Queries only.

---

## Where WordPress is allowed

| What | Where |
|---|---|
| `wpdb` | Infrastructure/Persistence only |
| `wp_schedule_event` | Infrastructure/Scheduler only |
| `register_rest_route` | Presentation/RestApi only |
| `add_action`, `add_filter` | Infrastructure/Hook and plugin bootstrap only |
| WP user system | Referenced by string ID in domain, resolved in Infrastructure |

---

## Test structure

```
tests/
├── Unit/          # Domain layer only, zero WP, uses Pest
├── Integration/   # Infrastructure layer, real DB, uses PHPUnit + WP test suite
├── Feature/       # Full flows via REST API
└── Factories/     # VendorFactory, OrderFactory, ProductFactory, PayoutFactory
```

Run unit tests: `./vendor/bin/pest tests/Unit`  
Run integration tests: `./vendor/bin/phpunit tests/Integration`

---

## Where to start (recommended TDD order)

The Order Split is the core domain logic. Start here:

1. `src/Order/Domain/Model/Commission.php` — value object, immutable, 0–100%
2. `src/Order/Domain/Model/OrderLine.php` — value object, captures price at order time
3. `src/Order/Domain/Model/VendorSubOrder.php` — entity, lifecycle invariants
4. `src/Order/Domain/Model/MarketplaceOrder.php` — aggregate root
5. `src/Order/Domain/Service/OrderSplitter.php` — groups lines by VendorId
6. `src/Order/Domain/Service/CommissionEngine.php` — calculates commission per sub-order

Write the test first. See `docs/domain/scenarios/order-flow.md` for invariants.

---

## Key documentation

- Actors and their scenarios: `docs/domain/actors/`
- All entities, invariants, behaviour: `docs/domain/entities/`
- Main order flow step by step: `docs/domain/scenarios/order-flow.md`
- Original project context: `docs/project-context.md`