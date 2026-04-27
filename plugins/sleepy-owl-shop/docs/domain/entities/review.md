# Entity: Review

**Context:** Review  
**Type:** Aggregate root  
**File:** `src/Review/Domain/Model/Review.php`

## Description
A buyer's rating and comment on a product and its vendor after delivery. Requires moderation before becoming visible. This is a custom domain entity — we do not rely on WooCommerce's built-in product reviews because we need vendor-level moderation and rating aggregation.

## Identity
`ReviewId` — UUID.

## State

| Field        | Type            | Notes                                               |
|--------------|-----------------|-----------------------------------------------------|
| `id`         | `ReviewId`      | Immutable                                           |
| `vendorId`   | `VendorId`      | Who is being reviewed (ID only)                     |
| `productId`  | `ProductId`     | Which product is being reviewed (ID only)           |
| `buyerRef`   | `string`        | WordPress user ID or anonymised reference           |
| `rating`     | `Rating`        | Value 1–5, immutable after submission               |
| `comment`    | `string`        | Text body of the review                             |
| `status`     | `ReviewStatus`  | `pending`, `approved`, `rejected`                  |
| `submittedAt`| `DateTimeImmutable` |                                                 |
| `moderatedAt`| `DateTimeImmutable\|null` |                                          |

## Invariants
- Rating must be between 1 and 5
- A buyer can submit only one review per product per order
- A rejected review cannot be approved without re-submission
- Comment cannot be empty

## Behaviour

| Method              | Description                                           |
|---------------------|-------------------------------------------------------|
| `submit()`          | Creates review in `pending`, raises `ReviewSubmitted` |
| `approve()`         | Manager approves, raises `ReviewApproved`             |
| `reject(reason)`    | Manager rejects, raises `ReviewRejected`              |

## Domain events raised
`ReviewSubmitted`, `ReviewApproved`, `ReviewRejected`

## Cross-context references
- `VendorId` — reference to Vendor context
- `ProductId` — reference to Catalog context
- Neither is fetched as an object — IDs are stored for read-model projection only