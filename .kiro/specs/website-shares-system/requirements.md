# Requirements Document

## Introduction

A backend website shares system for a Laravel 13 application. The system allows users to buy and sell shares in a website at a published price. All buy and sell requests require administrator approval before they are executed. Users cannot sell shares until a configurable holding period (default 30 days) has elapsed since acquisition. Dividends are periodically paid out to eligible shareholders — those who hold shares and have passed the holding period. All monetary movements use the existing wallet and transaction infrastructure (`HasWallets` concern, `Wallet` model, `Transaction` model). The system uses action classes, events, queued jobs, and notifications following the same architectural patterns as the loan management system.

## Glossary

- **Share**: A unit of ownership in a website, tracked by the `WebsiteShare` model.
- **ShareListing**: The current published price and available quantity of shares that can be bought or sold.
- **ShareOrder**: A request by a User to buy or sell a quantity of shares at the listed price, pending administrator approval.
- **ShareOrderStatus**: The lifecycle state of a `ShareOrder` — `pending`, `approved`, `rejected`.
- **ShareOrderType**: The direction of a `ShareOrder` — `buy` or `sell`.
- **ShareHolding**: A record of shares owned by a User, including the quantity and the date of acquisition (used to enforce the holding period).
- **HoldingPeriod**: The minimum number of days a User must hold shares before being permitted to sell them. Defaults to 30 days and is configurable.
- **Dividend**: A monetary payout distributed to eligible shareholders from a declared dividend amount.
- **DividendPayout**: A record of a single dividend payment made to a User for a specific dividend declaration.
- **ShareTransaction**: A `Transaction` record (via the existing `Transaction` model) linked to a share-related monetary movement — buy, sell, or dividend.
- **ShareHistory**: The auditable log of all share price changes and order activity over time.
- **Wallet**: The financial account managed by the `HasWallets` concern, used for all monetary movements.
- **HasWallets**: The existing Laravel concern on the `User` model providing `deposit()`, `withdraw()`, and `hold()` methods.
- **SharePolicy**: A Laravel Policy class that gates share order approval, rejection, and dividend declaration actions.
- **System**: The website shares system as a whole.

---

## Requirements

### Requirement 1: Share Listing and Current Price

**User Story:** As a user, I want to view the current share price and available quantity, so that I can make informed buy and sell decisions.

#### Acceptance Criteria

1. THE System SHALL maintain a `ShareListing` record storing the current share price, total issued shares, and available shares for purchase.
2. WHEN a User requests the current share listing, THE API SHALL return the current price, total issued shares, and available quantity.
3. WHEN an administrator updates the share price, THE System SHALL record the previous price and new price in a `SharePriceHistory` record with a timestamp.
4. THE ShareListing SHALL store the price as a decimal with two decimal places.

---

### Requirement 2: Buy Share Order Submission

**User Story:** As a user, I want to submit a request to buy shares, so that I can acquire ownership in the website.

#### Acceptance Criteria

1. WHEN a User submits a buy order with a valid quantity, THE System SHALL create a `ShareOrder` with type `buy` and status `pending`.
2. WHEN a buy order is created, THE System SHALL call `hold()` on the User via the `HasWallets` concern to reserve the total cost (quantity × current price) from the `General` wallet.
3. WHEN a buy order is created, THE System SHALL create a `Transaction` record linked to the hold, referencing the `ShareOrder`.
4. IF a User submits a buy order with a quantity less than 1, THEN THE System SHALL return a validation error.
5. IF a User's wallet has insufficient available balance to cover the total cost, THEN THE System SHALL return an error and SHALL NOT create the `ShareOrder`.
6. IF the requested quantity exceeds the available shares in the `ShareListing`, THEN THE System SHALL return an error and SHALL NOT create the `ShareOrder`.
7. WHEN a buy order is created, THE System SHALL dispatch a `ShareOrderPlaced` event.

---

### Requirement 3: Sell Share Order Submission

**User Story:** As a user, I want to submit a request to sell my shares, so that I can liquidate my holdings.

#### Acceptance Criteria

1. WHEN a User submits a sell order with a valid quantity, THE System SHALL create a `ShareOrder` with type `sell` and status `pending`.
2. IF a User submits a sell order for a quantity greater than their current `ShareHolding` quantity, THEN THE System SHALL return an error and SHALL NOT create the `ShareOrder`.
3. IF a User submits a sell order for shares that have not passed the `HoldingPeriod` since acquisition, THEN THE System SHALL return an error and SHALL NOT create the `ShareOrder`.
4. IF a User submits a sell order with a quantity less than 1, THEN THE System SHALL return a validation error.
5. WHEN a sell order is created, THE System SHALL dispatch a `ShareOrderPlaced` event.
6. WHEN a sell order is created, THE System SHALL create a `Transaction` record referencing the `ShareOrder` with status `pending`.

---

### Requirement 4: Buy Order Approval

**User Story:** As an administrator, I want to approve a pending buy order, so that the user's share acquisition is confirmed.

#### Acceptance Criteria

1. WHEN an administrator approves a buy order with status `pending`, THE System SHALL transition the `ShareOrder` status to `approved`.
2. WHEN a buy order is approved, THE System SHALL call `confirm()` on the associated hold `Transaction` to debit the funds from the User's wallet.
3. WHEN a buy order is approved, THE System SHALL create or increment a `ShareHolding` record for the User, recording the quantity and the acquisition timestamp.
4. WHEN a buy order is approved, THE System SHALL decrement the available shares in the `ShareListing` by the purchased quantity.
5. WHEN a buy order is approved, THE System SHALL dispatch a `ShareOrderApproved` event.
6. WHEN a buy order is approved, THE System SHALL send a `ShareOrderApprovedNotification` to the User.
7. IF an administrator attempts to approve a buy order that is not in `pending` status, THEN THE System SHALL return an error with HTTP 422.

---

### Requirement 5: Sell Order Approval

**User Story:** As an administrator, I want to approve a pending sell order, so that the user receives payment for their shares.

#### Acceptance Criteria

1. WHEN an administrator approves a sell order with status `pending`, THE System SHALL transition the `ShareOrder` status to `approved`.
2. WHEN a sell order is approved, THE System SHALL call `deposit()` on the User via the `HasWallets` concern, crediting the sale proceeds (quantity × current price) to the `General` wallet.
3. WHEN a sell order is approved, THE System SHALL decrement the User's `ShareHolding` quantity by the sold amount.
4. WHEN a sell order is approved, THE System SHALL increment the available shares in the `ShareListing` by the sold quantity.
5. WHEN a sell order is approved, THE System SHALL create a `Transaction` record for the deposit, referencing the `ShareOrder`.
6. WHEN a sell order is approved, THE System SHALL dispatch a `ShareOrderApproved` event.
7. WHEN a sell order is approved, THE System SHALL send a `ShareOrderApprovedNotification` to the User.
8. IF an administrator attempts to approve a sell order that is not in `pending` status, THEN THE System SHALL return an error with HTTP 422.

---

### Requirement 6: Share Order Rejection

**User Story:** As an administrator, I want to reject a pending share order, so that invalid or undesirable requests are declined.

#### Acceptance Criteria

1. WHEN an administrator rejects a `ShareOrder` with status `pending`, THE System SHALL transition the `ShareOrder` status to `rejected`.
2. WHEN a buy order is rejected, THE System SHALL call `void()` on the associated hold `Transaction` to release the reserved funds back to the User's wallet.
3. WHEN a share order is rejected, THE System SHALL dispatch a `ShareOrderRejected` event.
4. WHEN a share order is rejected, THE System SHALL send a `ShareOrderRejectedNotification` to the User.
5. IF an administrator attempts to reject a `ShareOrder` that is not in `pending` status, THEN THE System SHALL return an error with HTTP 422.
6. WHEN an administrator rejects a share order, THE System SHALL require a `rejection_reason` to be provided and store it on the `ShareOrder` record.

---

### Requirement 7: Holding Period Enforcement

**User Story:** As a system administrator, I want a configurable holding period enforced on all sell orders, so that users cannot immediately flip shares.

#### Acceptance Criteria

1. THE System SHALL enforce a default holding period of 30 days between share acquisition and the earliest permitted sell order submission.
2. THE HoldingPeriod SHALL be configurable via application configuration (e.g., `config('shares.holding_period_days')`).
3. WHEN a User submits a sell order, THE System SHALL verify that the acquisition timestamp on the User's `ShareHolding` is at least `HoldingPeriod` days in the past.
4. IF the holding period has not elapsed, THEN THE System SHALL return an error message indicating the earliest date the User may sell.
5. THE System SHALL calculate holding period eligibility based on the acquisition date of the earliest eligible batch of shares.

---

### Requirement 8: Share Holdings and Balance

**User Story:** As a user, I want to view my current share holdings and balance, so that I can track my ownership.

#### Acceptance Criteria

1. WHEN a User requests their share holdings, THE API SHALL return the total quantity of shares owned, the acquisition date, and the current market value (quantity × current price).
2. THE System SHALL expose the User's share holdings via a dedicated API endpoint.
3. WHEN a User has no shares, THE API SHALL return a holdings response with a quantity of zero.
4. THE System SHALL indicate whether the User's holdings have passed the holding period and are eligible for sale.

---

### Requirement 9: Share Order History

**User Story:** As a user, I want to view my share order history, so that I can audit my past buy and sell activity.

#### Acceptance Criteria

1. WHEN a User requests their share order history, THE API SHALL return all `ShareOrder` records belonging to the authenticated User, ordered by `created_at` descending.
2. THE API SHALL include the order type, quantity, price at time of order, status, and timestamps in each `ShareOrder` response.
3. THE API SHALL paginate share order history at 15 records per page by default.
4. IF a User requests a `ShareOrder` that does not belong to them, THEN THE API SHALL return HTTP 404.

---

### Requirement 10: Share Price History

**User Story:** As a user, I want to view the historical share prices, so that I can understand price trends over time.

#### Acceptance Criteria

1. THE System SHALL maintain a `SharePriceHistory` table recording each price change with the old price, new price, and the timestamp of the change.
2. WHEN a User requests the share price history, THE API SHALL return all `SharePriceHistory` records ordered by timestamp descending.
3. THE API SHALL paginate share price history at 15 records per page by default.

---

### Requirement 11: Dividend Declaration and Distribution

**User Story:** As an administrator, I want to declare and distribute dividends to eligible shareholders, so that share owners are rewarded for their investment.

#### Acceptance Criteria

1. WHEN an administrator declares a dividend with a total payout amount, THE System SHALL create a `Dividend` record storing the total amount, declaration timestamp, and status.
2. WHEN a dividend is declared, THE System SHALL dispatch a `ProcessDividendPayoutsJob` to distribute the dividend asynchronously.
3. WHEN the dividend job runs, THE System SHALL identify all Users with a `ShareHolding` quantity greater than zero whose acquisition date is at least `HoldingPeriod` days in the past.
4. WHEN the dividend job runs, THE System SHALL calculate each eligible User's proportional share of the total dividend amount based on their holding quantity relative to total eligible shares.
5. WHEN the dividend job runs, THE System SHALL call `deposit()` on each eligible User via the `HasWallets` concern, crediting their proportional dividend amount to the `General` wallet.
6. WHEN the dividend job runs, THE System SHALL create a `DividendPayout` record for each eligible User recording the User, dividend, amount paid, and the `Transaction` reference.
7. WHEN all payouts are processed, THE System SHALL update the `Dividend` record status to `distributed`.
8. IF no eligible shareholders exist at the time of distribution, THE System SHALL mark the `Dividend` as `distributed` with zero payouts.
9. WHEN a dividend is declared, THE System SHALL dispatch a `DividendDeclared` event.

---

### Requirement 12: Dividend Payout History

**User Story:** As a user, I want to view my dividend payout history, so that I can track income received from my shares.

#### Acceptance Criteria

1. WHEN a User requests their dividend payout history, THE API SHALL return all `DividendPayout` records belonging to the authenticated User, ordered by `created_at` descending.
2. THE API SHALL include the dividend amount, payout amount, and timestamp in each `DividendPayout` response.
3. THE API SHALL paginate dividend payout history at 15 records per page by default.

---

### Requirement 13: Transaction Recording

**User Story:** As a developer, I want all monetary movements to be recorded as `Transaction` records, so that there is a complete financial audit trail.

#### Acceptance Criteria

1. WHEN a buy order hold is created, THE System SHALL create a `Transaction` record with type `hold` and status `pending`, referencing the `ShareOrder` in the `meta` field.
2. WHEN a buy order is approved, THE System SHALL confirm the hold `Transaction`, transitioning it to status `completed`.
3. WHEN a buy order is rejected, THE System SHALL void the hold `Transaction`, transitioning it to status `voided`.
4. WHEN a sell order is approved, THE System SHALL create a `Transaction` record with type `deposit` and status `completed`, referencing the `ShareOrder` in the `meta` field.
5. WHEN a dividend payout is processed, THE System SHALL create a `Transaction` record with type `deposit` and status `completed`, referencing the `Dividend` and `DividendPayout` in the `meta` field.
6. THE System SHALL store a unique reference string on every `Transaction` record created by the shares system.

---

### Requirement 14: Events and Notifications

**User Story:** As a developer, I want share lifecycle changes to emit events and send queueable notifications, so that the system is decoupled and users are kept informed.

#### Acceptance Criteria

1. THE System SHALL dispatch a `ShareOrderPlaced` event when a buy or sell order is created.
2. THE System SHALL dispatch a `ShareOrderApproved` event when a buy or sell order is approved.
3. THE System SHALL dispatch a `ShareOrderRejected` event when a buy or sell order is rejected.
4. THE System SHALL dispatch a `DividendDeclared` event when a dividend is declared.
5. THE System SHALL implement a `ShareOrderApprovedNotification` class that implements `ShouldQueue` and is sent to the User when their order is approved.
6. THE System SHALL implement a `ShareOrderRejectedNotification` class that implements `ShouldQueue` and is sent to the User when their order is rejected.
7. THE System SHALL implement a `DividendPaidNotification` class that implements `ShouldQueue` and is sent to each User when a dividend payout is credited to their wallet.

---

### Requirement 15: Authorization via SharePolicy

**User Story:** As a system administrator, I want every sensitive share action to be gated by a policy, so that only authorised users can approve, reject, or declare dividends.

#### Acceptance Criteria

1. THE SharePolicy SHALL define gates for `approve`, `reject`, and `declareDividend` actions.
2. WHEN any approval, rejection, or dividend declaration action is invoked, THE System SHALL authorize the request against the corresponding `SharePolicy` gate before executing the action.
3. IF a User is not authorized for a given action, THEN THE API SHALL return HTTP 403.
4. THE SharePolicy SHALL be registered and applied consistently across all share administration controllers.

---

### Requirement 16: Action Classes

**User Story:** As a developer, I want all share business logic encapsulated in dedicated action classes, so that the system is testable and follows single-responsibility principles.

#### Acceptance Criteria

1. THE System SHALL implement a `PlaceBuyOrderAction` class responsible for validating and creating a buy `ShareOrder` and placing the wallet hold.
2. THE System SHALL implement a `PlaceSellOrderAction` class responsible for validating holding period eligibility and creating a sell `ShareOrder`.
3. THE System SHALL implement a `ApproveBuyOrderAction` class responsible for confirming the hold transaction, updating holdings, and updating the share listing.
4. THE System SHALL implement a `ApproveSellOrderAction` class responsible for depositing proceeds, updating holdings, and updating the share listing.
5. THE System SHALL implement a `RejectShareOrderAction` class responsible for voiding any hold transaction and updating the order status.
6. THE System SHALL implement a `DeclareDividendAction` class responsible for creating the `Dividend` record and dispatching the distribution job.
7. WHEN any action class encounters an invalid state, THE System SHALL throw a domain-specific exception rather than returning a boolean.
