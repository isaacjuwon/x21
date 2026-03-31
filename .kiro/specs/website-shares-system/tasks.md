# Implementation Plan: Website Shares System

## Overview

Implement a backend website shares system on Laravel 13 / PHP 8.4 with SQLite and Pest v4. Tasks are ordered by dependency: migrations → enums/config → models → exceptions → actions → events/jobs/notifications → form requests → resources → controllers → routes → tests.

## Tasks

- [x] 1. Database migrations
  - [x] 1.1 Create the `share_listings` table migration
    - Run `php artisan make:migration create_share_listings_table`
    - Columns: `id`, `price` decimal(15,2), `total_shares` unsignedBigInteger, `available_shares` unsignedBigInteger, timestamps
    - _Requirements: 1.1, 1.4_

  - [x] 1.2 Create the `share_price_histories` table migration
    - Run `php artisan make:migration create_share_price_histories_table`
    - Columns: `id`, `old_price` decimal(15,2), `new_price` decimal(15,2), `created_at` timestamp only (no `updated_at`)
    - _Requirements: 1.3, 10.1_

  - [x] 1.3 Create the `share_orders` table migration
    - Run `php artisan make:migration create_share_orders_table`
    - Columns: `id`, `user_id` (FK→users), `type` string, `quantity` unsignedBigInteger, `price_per_share` decimal(15,2), `total_amount` decimal(15,2), `status` string default `pending`, `hold_transaction_id` nullable bigint (FK→transactions nullOnDelete), `rejection_reason` nullable text, timestamps
    - _Requirements: 2.1, 3.1, 9.2_

  - [x] 1.4 Create the `share_holdings` table migration
    - Run `php artisan make:migration create_share_holdings_table`
    - Columns: `id`, `user_id` (FK→users unique), `quantity` unsignedBigInteger default 0, `acquired_at` timestamp nullable, timestamps
    - _Requirements: 4.3, 7.3, 8.1_

  - [x] 1.5 Create the `dividends` table migration
    - Run `php artisan make:migration create_dividends_table`
    - Columns: `id`, `total_amount` decimal(15,2), `status` string default `pending`, `declared_at` timestamp, timestamps
    - _Requirements: 11.1_

  - [x] 1.6 Create the `dividend_payouts` table migration
    - Run `php artisan make:migration create_dividend_payouts_table`
    - Columns: `id`, `dividend_id` (FK→dividends cascadeOnDelete), `user_id` (FK→users), `amount` decimal(15,2), `transaction_id` nullable bigint (FK→transactions nullOnDelete), timestamps
    - _Requirements: 11.6, 12.1_

- [x] 2. Enums and configuration
  - [x] 2.1 Create the two share enums under `App\Enums\Shares\`
    - Run `php artisan make:enum Enums/Shares/ShareOrderStatus` — cases: `Pending='pending'`, `Approved='approved'`, `Rejected='rejected'`
    - Run `php artisan make:enum Enums/Shares/ShareOrderType` — cases: `Buy='buy'`, `Sell='sell'`
    - Run `php artisan make:enum Enums/Shares/DividendStatus` — cases: `Pending='pending'`, `Distributed='distributed'`
    - _Requirements: 2.1, 3.1, 11.1_

  - [x] 2.2 Create `config/shares.php`
    - Keys: `holding_period_days` (integer, default 30)
    - _Requirements: 7.1, 7.2_

- [x] 3. Eloquent models and factories
  - [x] 3.1 Create the `ShareListing` model
    - Run `php artisan make:model ShareListing --factory`
    - `$fillable`: `price`, `total_shares`, `available_shares`
    - Cast `price` to `decimal:2`
    - Factory: realistic price (e.g. 10.00–500.00), total_shares (e.g. 1000), available_shares same as total_shares
    - _Requirements: 1.1_

  - [x] 3.2 Create the `SharePriceHistory` model
    - Run `php artisan make:model SharePriceHistory`
    - `$timestamps = false`, `const CREATED_AT = 'created_at'`
    - `$fillable`: `old_price`, `new_price`, `created_at`
    - _Requirements: 1.3, 10.1_

  - [x] 3.3 Create the `ShareOrder` model
    - Run `php artisan make:model ShareOrder --factory`
    - `$fillable`: `user_id`, `type`, `quantity`, `price_per_share`, `total_amount`, `status`, `hold_transaction_id`, `rejection_reason`
    - Cast `status` to `ShareOrderStatus`, `type` to `ShareOrderType`
    - Relationships: `belongsTo(User::class)`, `belongsTo(Transaction::class, 'hold_transaction_id')`
    - Factory: generate valid buy/sell orders with `status = ShareOrderStatus::Pending`; add states `approved()`, `rejected()`; add `buy()` and `sell()` type states
    - _Requirements: 2.1, 3.1_

  - [x] 3.4 Create the `ShareHolding` model
    - Run `php artisan make:model ShareHolding --factory`
    - `$fillable`: `user_id`, `quantity`, `acquired_at`
    - Cast `acquired_at` to `datetime`
    - Relationship: `belongsTo(User::class)`
    - Factory: quantity (1–1000), acquired_at (e.g. 60 days ago by default)
    - _Requirements: 4.3, 8.1_

  - [x] 3.5 Create the `Dividend` model
    - Run `php artisan make:model Dividend --factory`
    - `$fillable`: `total_amount`, `status`, `declared_at`
    - Cast `status` to `DividendStatus`, `declared_at` to `datetime`
    - Relationship: `hasMany(DividendPayout::class)`
    - Factory: total_amount (100–10000), status = DividendStatus::Pending, declared_at = now()
    - _Requirements: 11.1_

  - [x] 3.6 Create the `DividendPayout` model
    - Run `php artisan make:model DividendPayout --factory`
    - `$fillable`: `dividend_id`, `user_id`, `amount`, `transaction_id`
    - Relationships: `belongsTo(Dividend::class)`, `belongsTo(User::class)`, `belongsTo(Transaction::class)`
    - _Requirements: 11.6, 12.1_

  - [x] 3.7 Add `shareHolding()`, `shareOrders()`, and `dividendPayouts()` relationships to the `User` model
    - Edit `app/Models/User.php`
    - Add `hasOne(ShareHolding::class)`, `hasMany(ShareOrder::class)`, `hasMany(DividendPayout::class)`
    - _Requirements: 8.1, 9.1, 12.1_

- [x] 4. Exceptions
  - [x] 4.1 Create `InvalidShareOrderStateException` under `App\Exceptions\Shares\`
    - Extends `\RuntimeException`
    - _Requirements: 4.7, 5.8, 6.5, 16.7_

  - [x] 4.2 Create `InsufficientSharesException` under `App\Exceptions\Shares\`
    - Extends `\RuntimeException`
    - _Requirements: 3.2, 16.7_

  - [x] 4.3 Create `HoldingPeriodNotMetException` under `App\Exceptions\Shares\`
    - Extends `\RuntimeException`; constructor accepts `Carbon $earliestSellDate`; exposes `getEarliestSellDate(): Carbon`
    - _Requirements: 3.3, 7.4, 16.7_

  - [x] 4.4 Create `InsufficientAvailableSharesException` under `App\Exceptions\Shares\`
    - Extends `\RuntimeException`
    - _Requirements: 2.6, 16.7_

  - [x] 4.5 Register exception renderers in `bootstrap/app.php`
    - Add handlers for `InvalidShareOrderStateException` → 422 JSON, `InsufficientSharesException` → 422 JSON, `HoldingPeriodNotMetException` → 422 JSON with earliest sell date, `InsufficientAvailableSharesException` → 422 JSON
    - _Requirements: 4.7, 5.8, 6.5_

- [x] 5. Events
  - [x] 5.1 Create share lifecycle events under `App\Events\Shares\`
    - `php artisan make:event Events/Shares/ShareOrderPlaced` — constructor: `public ShareOrder $order`
    - `php artisan make:event Events/Shares/ShareOrderApproved` — constructor: `public ShareOrder $order`
    - `php artisan make:event Events/Shares/ShareOrderRejected` — constructor: `public ShareOrder $order`
    - `php artisan make:event Events/Shares/DividendDeclared` — constructor: `public Dividend $dividend`
    - Each uses `Dispatchable`, `InteractsWithSockets`, `SerializesModels`
    - _Requirements: 14.1, 14.2, 14.3, 14.4_

- [x] 6. Notifications
  - [x] 6.1 Create queueable share notifications under `App\Notifications\Shares\`
    - `php artisan make:notification Notifications/Shares/ShareOrderApprovedNotification` — implements `ShouldQueue`, accepts `public ShareOrder $order`, `via()` returns `['database']`
    - `php artisan make:notification Notifications/Shares/ShareOrderRejectedNotification` — implements `ShouldQueue`, accepts `public ShareOrder $order`, `via()` returns `['database']`
    - `php artisan make:notification Notifications/Shares/DividendPaidNotification` — implements `ShouldQueue`, accepts `public DividendPayout $payout`, `via()` returns `['database']`
    - _Requirements: 14.5, 14.6, 14.7_

- [x] 7. Queued job
  - [x] 7.1 Create `ProcessDividendPayoutsJob` under `App\Jobs\`
    - Run `php artisan make:job Jobs/ProcessDividendPayoutsJob`
    - Constructor: `public Dividend $dividend`
    - `handle()`: queries eligible `ShareHolding` records (quantity > 0, acquired_at ≥ holding_period_days ago), calculates proportional payout per user, calls `deposit()` on each user, creates `DividendPayout` records, sends `DividendPaidNotification`, marks `Dividend` as `distributed`
    - _Requirements: 11.2, 11.3, 11.4, 11.5, 11.6, 11.7, 11.8_

- [x] 8. Action classes under `App\Actions\Shares\`
  - [x] 8.1 Create `PlaceBuyOrderAction`
    - `handle(User $user, int $quantity): ShareOrder`
    - Validates quantity ≥ 1; checks available shares in `ShareListing`; calls `$user->hold(total_cost, WalletType::General)`; creates `ShareOrder` with `hold_transaction_id`; dispatches `ShareOrderPlaced`
    - Throws `InsufficientAvailableSharesException` if not enough shares available
    - _Requirements: 2.1, 2.2, 2.3, 2.5, 2.6, 2.7, 13.1_

  - [x] 8.2 Create `PlaceSellOrderAction`
    - `handle(User $user, int $quantity): ShareOrder`
    - Validates quantity ≥ 1; checks user's `ShareHolding` quantity; checks holding period via `acquired_at`; creates `ShareOrder` with type `sell`; dispatches `ShareOrderPlaced`
    - Throws `InsufficientSharesException` if quantity exceeds holding; throws `HoldingPeriodNotMetException` with earliest sell date if period not met
    - _Requirements: 3.1, 3.2, 3.3, 3.5, 3.6, 7.3, 7.4, 7.5_

  - [x] 8.3 Create `ApproveBuyOrderAction`
    - `handle(ShareOrder $order, User $actor): ShareOrder`
    - Throws `InvalidShareOrderStateException` if status ≠ `Pending`; calls `$order->holdTransaction->confirm()`; creates/increments `ShareHolding` for user (set `acquired_at = now()` if new); decrements `ShareListing::available_shares`; transitions order to `Approved`; dispatches `ShareOrderApproved`; sends `ShareOrderApprovedNotification`
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 13.2_

  - [x] 8.4 Create `ApproveSellOrderAction`
    - `handle(ShareOrder $order, User $actor): ShareOrder`
    - Throws `InvalidShareOrderStateException` if status ≠ `Pending`; calls `$order->user->deposit(proceeds, WalletType::General)`; decrements `ShareHolding` quantity; increments `ShareListing::available_shares`; transitions order to `Approved`; dispatches `ShareOrderApproved`; sends `ShareOrderApprovedNotification`
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 13.4_

  - [x] 8.5 Create `RejectShareOrderAction`
    - `handle(ShareOrder $order, User $actor, string $rejectionReason): ShareOrder`
    - Throws `InvalidShareOrderStateException` if status ≠ `Pending`; if buy order, calls `$order->holdTransaction->void()`; stores `rejection_reason`; transitions to `Rejected`; dispatches `ShareOrderRejected`; sends `ShareOrderRejectedNotification`
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 13.3_

  - [x] 8.6 Create `DeclareDividendAction`
    - `handle(float $amount): Dividend`
    - Creates `Dividend` record with `total_amount`, `status = pending`, `declared_at = now()`; dispatches `DividendDeclared` event; dispatches `ProcessDividendPayoutsJob`; returns the `Dividend`
    - _Requirements: 11.1, 11.2, 11.9_

  - [x] 8.7 Create `UpdateSharePriceAction`
    - `handle(float $newPrice): ShareListing`
    - Loads the current `ShareListing`; creates a `SharePriceHistory` record with old/new price; updates `ShareListing::price`; returns updated listing
    - _Requirements: 1.3_

- [x] 9. SharePolicy
  - [x] 9.1 Create `SharePolicy` under `App\Policies\`
    - Run `php artisan make:policy SharePolicy --no-interaction`
    - `approve(User $user, ShareOrder $order): bool` — return `(bool) $user->is_admin`
    - `reject(User $user, ShareOrder $order): bool` — return `(bool) $user->is_admin`
    - `declareDividend(User $user): bool` — return `(bool) $user->is_admin`
    - `updatePrice(User $user): bool` — return `(bool) $user->is_admin`
    - _Requirements: 15.1, 15.2, 15.3, 15.4_

- [x] 10. Form requests
  - [x] 10.1 Create `StoreBuyOrderRequest`
    - Run `php artisan make:request Http/Requests/Api/V1/Shares/StoreBuyOrderRequest`
    - Rules: `quantity` (required, integer, min:1)
    - _Requirements: 2.4_

  - [x] 10.2 Create `StoreSellOrderRequest`
    - Run `php artisan make:request Http/Requests/Api/V1/Shares/StoreSellOrderRequest`
    - Rules: `quantity` (required, integer, min:1)
    - _Requirements: 3.4_

  - [x] 10.3 Create `StoreShareOrderRejectionRequest`
    - Run `php artisan make:request Http/Requests/Api/V1/Shares/StoreShareOrderRejectionRequest`
    - Rules: `rejection_reason` (required, string)
    - _Requirements: 6.6_

  - [x] 10.4 Create `StoreDividendRequest`
    - Run `php artisan make:request Http/Requests/Api/V1/Shares/StoreDividendRequest`
    - Rules: `total_amount` (required, numeric, min:0.01)
    - _Requirements: 11.1_

  - [x] 10.5 Create `UpdateSharePriceRequest`
    - Run `php artisan make:request Http/Requests/Api/V1/Shares/UpdateSharePriceRequest`
    - Rules: `price` (required, numeric, min:0.01)
    - _Requirements: 1.3_

- [x] 11. API Resources
  - [x] 11.1 Create `ShareListingResource`
    - Run `php artisan make:resource Http/Resources/Api/V1/Shares/ShareListingResource`
    - Expose: `id`, `price`, `total_shares`, `available_shares`, `updated_at`
    - _Requirements: 1.2_

  - [x] 11.2 Create `ShareOrderResource`
    - Run `php artisan make:resource Http/Resources/Api/V1/Shares/ShareOrderResource`
    - Expose: `id`, `user_id`, `type`, `quantity`, `price_per_share`, `total_amount`, `status`, `rejection_reason`, `created_at`
    - _Requirements: 9.2_

  - [x] 11.3 Create `ShareHoldingResource`
    - Run `php artisan make:resource Http/Resources/Api/V1/Shares/ShareHoldingResource`
    - Expose: `quantity`, `acquired_at`, `market_value` (quantity × current price), `eligible_for_sale` (bool: acquired_at ≥ holding_period_days ago)
    - _Requirements: 8.1, 8.4_

  - [x] 11.4 Create `SharePriceHistoryResource`
    - Run `php artisan make:resource Http/Resources/Api/V1/Shares/SharePriceHistoryResource`
    - Expose: `id`, `old_price`, `new_price`, `created_at`
    - _Requirements: 10.1_

  - [x] 11.5 Create `DividendPayoutResource`
    - Run `php artisan make:resource Http/Resources/Api/V1/Shares/DividendPayoutResource`
    - Expose: `id`, `amount`, `created_at`, and nested `dividend` with `total_amount` and `declared_at`
    - _Requirements: 12.2_

- [x] 12. Controllers under `App\Http\Controllers\Api\V1\Shares\`
  - [x] 12.1 Create `ShareListingController`
    - `show`: return `ShareListingResource` for the current listing (first or fail)
    - `update`: authorize `updatePrice`, use `UpdateSharePriceRequest`, call `UpdateSharePriceAction`, return `ShareListingResource`
    - _Requirements: 1.2, 1.3_

  - [x] 12.2 Create `ShareOrderController`
    - `index`: return `ShareOrderResource::collection(auth()->user()->shareOrders()->latest()->paginate(15))`
    - `show`: authorize ownership (return 404 if not owner), return `ShareOrderResource`
    - `buy`: use `StoreBuyOrderRequest`, call `PlaceBuyOrderAction`, return `ShareOrderResource` with 201
    - `sell`: use `StoreSellOrderRequest`, call `PlaceSellOrderAction`, return `ShareOrderResource` with 201
    - _Requirements: 2.1, 3.1, 9.1, 9.3, 9.4_

  - [x] 12.3 Create `ShareOrderApprovalController`
    - `store`: authorize `approve` on the order, call `ApproveBuyOrderAction` or `ApproveSellOrderAction` based on order type, return `ShareOrderResource`
    - _Requirements: 4.1, 5.1_

  - [x] 12.4 Create `ShareOrderRejectionController`
    - `store`: authorize `reject` on the order, use `StoreShareOrderRejectionRequest`, call `RejectShareOrderAction`, return `ShareOrderResource`
    - _Requirements: 6.1_

  - [x] 12.5 Create `ShareHoldingController`
    - `show`: return `ShareHoldingResource` for the authenticated user's holding (or empty holding with quantity 0 if none exists)
    - _Requirements: 8.1, 8.2, 8.3_

  - [x] 12.6 Create `SharePriceHistoryController`
    - `index`: return `SharePriceHistoryResource::collection(SharePriceHistory::latest('created_at')->paginate(15))`
    - _Requirements: 10.2, 10.3_

  - [x] 12.7 Create `DividendController`
    - `store`: authorize `declareDividend`, use `StoreDividendRequest`, call `DeclareDividendAction`, return JSON 201
    - _Requirements: 11.1_

  - [x] 12.8 Create `DividendPayoutController`
    - `index`: return `DividendPayoutResource::collection(auth()->user()->dividendPayouts()->latest()->paginate(15))`
    - _Requirements: 12.1, 12.3_

- [x] 13. Routes
  - [x] 13.1 Add share routes to `routes/api.php`
    - Add under the existing `auth:sanctum` + `v1` group:
    - `GET shares/listing` → `ShareListingController@show`
    - `PUT shares/listing/price` → `ShareListingController@update`
    - `GET shares/orders` → `ShareOrderController@index`
    - `GET shares/orders/{order}` → `ShareOrderController@show`
    - `POST shares/orders/buy` → `ShareOrderController@buy`
    - `POST shares/orders/sell` → `ShareOrderController@sell`
    - `POST shares/orders/{order}/approve` → `ShareOrderApprovalController@store`
    - `POST shares/orders/{order}/reject` → `ShareOrderRejectionController@store`
    - `GET shares/holdings` → `ShareHoldingController@show`
    - `GET shares/price-history` → `SharePriceHistoryController@index`
    - `POST shares/dividends` → `DividendController@store`
    - `GET shares/dividends/payouts` → `DividendPayoutController@index`
    - _Requirements: 8.2, 9.1, 10.2, 11.1, 12.1_

- [ ] 14. Checkpoint — wire everything together
  - Run `php artisan migrate` and `php artisan route:list --path=api/v1/shares` to confirm all routes are registered and migrations run cleanly.

- [ ] 15. Feature tests — buy order
  - [ ] 15.1 Create `tests/Feature/Shares/BuyOrderTest.php`
    - Run `php artisan make:test --pest Feature/Shares/BuyOrderTest`
    - Test: valid buy order creates `ShareOrder` with `pending` status and `buy` type; wallet hold is created; `ShareOrderPlaced` event dispatched
    - Test: quantity < 1 returns 422
    - Test: insufficient wallet balance returns 422 without creating order
    - Test: quantity exceeds available shares returns 422 without creating order
    - _Requirements: 2.1, 2.2, 2.4, 2.5, 2.6, 2.7_

- [ ] 16. Feature tests — sell order
  - [ ] 16.1 Create `tests/Feature/Shares/SellOrderTest.php`
    - Run `php artisan make:test --pest Feature/Shares/SellOrderTest`
    - Test: valid sell order creates `ShareOrder` with `pending` status and `sell` type; `ShareOrderPlaced` event dispatched
    - Test: quantity < 1 returns 422
    - Test: quantity exceeds holding returns 422
    - Test: holding period not met returns 422 with earliest sell date
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 7.3, 7.4_

- [ ] 17. Feature tests — order approval
  - [ ] 17.1 Create `tests/Feature/Shares/OrderApprovalTest.php`
    - Run `php artisan make:test --pest Feature/Shares/OrderApprovalTest`
    - Test: approving pending buy order transitions to `approved`; hold confirmed; `ShareHolding` created/incremented; available shares decremented; `ShareOrderApproved` dispatched; notification sent
    - Test: approving pending sell order transitions to `approved`; deposit credited; holding decremented; available shares incremented; `ShareOrderApproved` dispatched
    - Test: approving non-pending order returns 422
    - Test: non-admin cannot approve (returns 403)
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 5.1, 5.2, 5.3, 5.4, 5.6, 5.7, 5.8_

- [ ] 18. Feature tests — order rejection
  - [ ] 18.1 Create `tests/Feature/Shares/OrderRejectionTest.php`
    - Run `php artisan make:test --pest Feature/Shares/OrderRejectionTest`
    - Test: rejecting pending buy order transitions to `rejected`; hold voided; `ShareOrderRejected` dispatched; notification sent
    - Test: rejecting pending sell order transitions to `rejected`; `ShareOrderRejected` dispatched
    - Test: rejecting non-pending order returns 422
    - Test: `rejection_reason` required (returns 422 if missing)
    - Test: non-admin cannot reject (returns 403)
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

- [ ] 19. Feature tests — holdings and listing
  - [ ] 19.1 Create `tests/Feature/Shares/HoldingsAndListingTest.php`
    - Run `php artisan make:test --pest Feature/Shares/HoldingsAndListingTest`
    - Test: user with no holding returns quantity 0
    - Test: user with holding returns correct quantity, market value, and eligibility flag
    - Test: share listing returns price and available shares
    - Test: admin can update share price; `SharePriceHistory` record created
    - _Requirements: 1.2, 1.3, 8.1, 8.3, 8.4_

- [ ] 20. Feature tests — dividends
  - [ ] 20.1 Create `tests/Feature/Shares/DividendTest.php`
    - Run `php artisan make:test --pest Feature/Shares/DividendTest`
    - Test: admin declares dividend; `Dividend` record created; `ProcessDividendPayoutsJob` dispatched; `DividendDeclared` event dispatched
    - Test: job distributes proportional payouts to eligible holders; wallets credited; `DividendPayout` records created; `DividendPaidNotification` sent
    - Test: ineligible holders (holding period not met) excluded from payouts
    - Test: no eligible holders marks dividend as `distributed` with zero payouts
    - Test: non-admin cannot declare dividend (returns 403)
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6, 11.7, 11.8, 11.9_

- [ ] 21. Feature tests — order history and price history
  - [ ] 21.1 Create `tests/Feature/Shares/OrderHistoryTest.php`
    - Run `php artisan make:test --pest Feature/Shares/OrderHistoryTest`
    - Test: user only sees own orders; cross-user order returns 404; list ordered by `created_at` desc; paginated at 15
    - Test: share price history returns records ordered by timestamp desc; paginated at 15
    - _Requirements: 9.1, 9.3, 9.4, 10.2, 10.3_

- [ ] 22. Architecture tests
  - [ ] 22.1 Add architecture tests to `tests/Arch/SharesTest.php`
    - Run `php artisan make:test --pest Arch/SharesTest`
    - Assert `App\Actions\Shares` classes have `Action` suffix
    - Assert `App\Notifications\Shares` classes implement `ShouldQueue`
    - _Requirements: 14.5, 14.6, 14.7, 16.1_

- [ ] 23. Final checkpoint — run full test suite
  - Run `php artisan test --compact` and ensure all tests pass. Run `vendor/bin/pint --dirty` to fix any style issues.

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP
- Each task references specific requirements for traceability
- Checkpoints at tasks 14 and 23 ensure incremental validation
- The `Transaction` model's `confirm()` and `void()` methods are already implemented and should be used directly
- The `HasWallets` concern's `hold()`, `deposit()`, and `withdraw()` methods are already implemented
- `ShareHolding` uses a single record per user — quantity is incremented/decremented rather than creating new records per purchase
