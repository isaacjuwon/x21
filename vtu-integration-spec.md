# VTU Integration Spec

Stack: Laravel 13, PHP 8.4, Livewire 4, Filament 5.

## 1. Goals

- Sell VTU products (airtime, data, cable, electricity) through multiple upstream providers with different API structures.
- Control our own selling price. Providers only supply cost.
- Keep providers thin: they translate requests and responses and nothing else.
- Customers and the frontend only know our identifiers, never provider codes.
- Never lose money on ambiguous outcomes: unknown means `pending`, never `failed`.
- Plan syncs are previewed by an admin before they are applied.

## 2. Constraint: the existing implementation does not change

The following already exist and are **not modified** by this spec:

| Existing class | Role |
|---|---|
| `ApiManager` | Entry point, resolves the provider chain |
| `FailoverProvider` | Reads providers from DB by priority, loops on failure |
| `EpinsProvider` (and future providers) | Implements `VtuProvider`, talks to the upstream |
| `EpinsConnector` | Base URL, auth, headers, timeout |
| `AirtimeResource` (and sibling resources) | One method per endpoint, returns raw `Response` |

Everything in this spec is **additive**. Where the design needs behavior that the existing classes may not have (plan fetching, requery, status mapping), it goes into **sidecar classes** registered per provider slug, so the existing provider classes stay untouched. Section 5 defines them.

> Assumption: I have not seen the existing code. Where this spec says "existing contract", it means whatever `ApiManager` and `FailoverProvider` already call. Adapt the sidecar interfaces to match, not the other way around.

## 3. Architecture

```
HTTP / Livewire / Filament
        |
   Actions (new)        PurchasePlan, FinalizeTransaction, RefundTransaction,
        |               PreviewPlanSync, ApplyPlanSync, RequeryPending
        v
   ApiManager (existing)
        v
   FailoverProvider (existing)   <- priority from DB
        v
   EpinsProvider (existing)  ...  OtherProvider
        v
   EpinsConnector -> Resources -> raw Response   (existing)

Sidecars (new, per provider slug)
   PlanSource       fetchRawPlans(), normalizePlans()
   ResultMapper     toResult(raw): PurchaseResult  (status map)
   StatusChecker    requery(reference)
   WebhookHandler   verify(), parse()   (optional)
```

Layer rules:

| Layer | Allowed | Forbidden |
|---|---|---|
| Connector | URL, auth, headers, timeout | Business logic |
| Resource | Endpoint paths, request params, returns raw `Response` | Normalizing, DB |
| Provider | Route by product type, call resource | Pricing, wallet, refunds, retries |
| Sidecars | Field mapping, status map, filling `meta` | HTTP calls other than via the connector, DB |
| FailoverProvider | Priority, failover on definite failure | Pricing, wallet |
| Actions | DB, wallet, pricing, state transitions | Provider-specific field names |

## 4. Data model

### 4.1 `providers`
```
id, slug (unique), name, is_active, priority,
config (encrypted json, nullable), created_at, updated_at
```
Already exists in some form since failover reads it. Add missing columns by migration only, do not rename existing ones.

### 4.2 `products` (our catalog, what customers buy)
```
id, uuid (unique, public), type (airtime|data|cable|electricity),
network, name, validity (nullable), amount (nullable, for fixed products),
selling_price, custom_price (nullable), is_active, sort_order
```
`uuid` is the only identifier the frontend sends.

### 4.3 `provider_plans` (a provider's offer for a product)
```
id, provider_id, product_id (nullable until mapped),
provider_plan_code (nullable, airtime has none),
network, name, type, cost_price,
meta (json), is_active, last_synced_at
unique(provider_id, provider_plan_code)
```
- `meta` holds whatever that provider needs at purchase time (network id, operator, variation code).
- If the client only ever uses one provider per product, `products` and `provider_plans` can be collapsed into one `plans` table. Keep them split if failover must substitute equivalent plans.
- Plans the provider stops returning are set `is_active = false`, never deleted.

### 4.4 `sync_previews`
```
id, provider_id, raw_payload (json), diff_summary (json),
status (pending|applied|expired|rejected),
created_by, expires_at, applied_at, applied_by
```

### 4.5 `transactions`
```
id, user_id, product_id, provider_plan_id (nullable until served),
provider_id (nullable until served),
reference (unique, ours), provider_reference (nullable),
type, recipient, amount,
cost_price, selling_price, profit (snapshots),
status (pending|success|failed|refunded),
token (nullable, electricity), message,
provider_request (json), provider_response (json),
attempts_count, completed_at, created_at
index(status, created_at)
```

### 4.6 `transaction_attempts`
```
id, transaction_id, provider_id, provider_plan_id,
attempt_reference (unique), status, request (json), response (json),
created_at
```
One row per provider tried. Requery and webhooks resolve through `attempt_reference`, so they hit the right provider.

### 4.7 Wallet
```
wallets: id, user_id, balance
wallet_ledger: id, wallet_id, transaction_id, type (debit|refund),
               amount, balance_after, unique(transaction_id, type)
```
The unique `(transaction_id, type)` makes refunds idempotent at the database level.

### 4.8 `provider_balances` (optional)
```
provider_id, balance, last_checked_at
```

## 5. Contracts and DTOs (new)

### 5.1 DTOs (readonly)
```php
PlanData        providerCode, network, name, type, costPrice, meta[]
PurchaseData    product, providerPlan, recipient, reference, amount
PurchaseResult  status (success|pending|failed), providerReference,
                message, token, raw[], servedBy
SyncDiff        new[], changed[], removed[], warnings[]
```
`PurchaseResult` has named constructors: `success()`, `pending()`, `failed()`.

### 5.2 Sidecar interfaces
```php
interface PlanSource {
    public function fetchRawPlans(): array;                  // via connector, no DB
    public function normalizePlans(array $raw): Collection;  // pure, returns PlanData
}

interface ResultMapper {
    public function toResult(Response|array $raw): PurchaseResult;  // pure
}

interface StatusChecker {
    public function requery(string $attemptReference, string $type): PurchaseResult;
}

interface WebhookHandler {
    public function verify(Request $request): bool;
    public function parse(Request $request): PurchaseResult;  // + attempt reference
}

interface ValidatesRecipient {   // optional, meter / smartcard lookups
    public function validate(string $type, string $recipient, array $context): RecipientInfo;
}
```

### 5.3 Registry
```php
ProviderSidecars::for(string $slug): object  // returns PlanSource, ResultMapper, ...
```
A new provider is registered with one line in a service provider, for example:
```php
$registry->register('epins', plans: EpinsPlanSource::class,
    results: EpinsResultMapper::class, status: EpinsStatusChecker::class);
```
Sidecars take the provider's existing `Connector` and call its resources. They add no new HTTP code paths.

### 5.4 Per-provider classes (Epins example, all new)
```
app/Vtu/Providers/Epins/
  EpinsPlanSource.php       fetchRawPlans() -> connector resources; normalizePlans()
  EpinsResultMapper.php     status map + providerReference + token extraction
  EpinsStatusChecker.php    calls the resource status() endpoint, maps via ResultMapper
  EpinsWebhookHandler.php   optional
```

## 6. Result mapping rules

- A single `statusMap()` per provider, defined only in its `ResultMapper`.
- Only codes the provider **documents** as definite failures map to `failed` (invalid number, invalid plan, provider out of balance).
- Everything unrecognized, malformed, or empty maps to `pending`.
- Timeouts, connection errors, and 5xx responses map to `pending`.
- `raw` is always kept and stored in `provider_response`.
- The same mapper serves purchase, requery, and webhook paths.

Where the existing provider already returns a `PurchaseResult`, the sidecar is not used on that path. The sidecar is used for requery, webhooks, and anything the existing provider does not cover.

## 7. Pricing

`PricingService::calculate(ProviderPlan $plan): int|string` (Money type in use by the project)

Order of precedence:
1. `products.custom_price`, if set (manual override)
2. Rule-based: percentage, flat, or tiered by cost
3. Rounded to nearest 5 or 10 naira

Guards:
- Result is never below `cost_price`.
- A change of more than 30% in `cost_price` is flagged in the sync diff, not silently applied.
- Per-user-level markups (regular, agent, reseller) apply on top of the base price at purchase time through a `price_levels` table. Optional for v1.

Prices come from the database only. The request never carries a price.

## 8. Plan sync

### 8.1 Preview (`PreviewPlanSync`)
1. `PlanSource::fetchRawPlans()` for the provider.
2. Abort if the response is an error or empty.
3. `normalizePlans($raw)` for display and diffing only.
4. Diff against `provider_plans`: new, price changed (old, new, % change), removed.
5. Compute the selling price each row would get, flag rows below cost or with big jumps.
6. Store a `sync_previews` row with `raw_payload` and `expires_at` (30 minutes).

### 8.2 Apply (`ApplyPlanSync`)
1. Reject if expired or already applied (lock the preview row).
2. Run `normalizePlans($preview->raw_payload)`. **Normalization happens here**, on the stored raw data, never on a refetch.
3. Abort if the result is empty.
4. In one transaction: upsert `provider_plans` by `(provider_id, provider_plan_code)`, refresh selling prices, deactivate plans no longer returned, mark the preview applied.

`normalizePlans()` validates rows: missing code or non-numeric price skips the row and adds a warning.

### 8.3 Scheduled sync
A `SyncProviderPlans` job per provider (one failing provider never blocks another).
- Auto-apply only if the diff is safe: no removals, no price jump over the threshold, no warnings.
- Otherwise keep the preview as `pending` and notify an admin.
- First onboarding of any provider is always a manual preview.

### 8.4 Admin UI (Filament)
A page per provider: "Fetch preview", tabs for Raw JSON, Mapped, and Diff, and a "Confirm sync" button. Resource for mapping unmapped `provider_plans` to `products`.

## 9. Purchase flow

Endpoint: `POST /purchase { product: <uuid>, recipient, amount? }`

`PurchasePlan` action:
1. Resolve the product by `uuid`, must be active.
2. Validate the recipient format for the network or type.
3. Resolve candidate provider plans ordered by provider priority (the same ordering `FailoverProvider` already uses).
4. Margin check: skip candidates where `cost_price >= selling_price`. If none remain, fail without debiting.
5. DB transaction: lock the wallet row (`lockForUpdate`), check balance, create the `transactions` row as `pending` with price snapshots, debit the wallet through the ledger.
6. Dispatch `ProcessPurchaseJob` (after commit).

`ProcessPurchaseJob`:
1. Create a `transaction_attempts` row with its own `attempt_reference`.
2. Call `ApiManager` (existing), which goes through `FailoverProvider` and the provider.
3. Pass the `PurchaseResult` to `FinalizeTransaction`.

Never call the provider inside the DB transaction.

## 10. Failover rules (enforced by what we add around the existing class)

The existing `FailoverProvider` owns the loop. This spec adds requirements it must satisfy, and the checks that sit around it:

- Only a definite `failed` moves to the next provider.
- `pending` and timeouts stop the chain.
- Business failures that every provider will reject (invalid number) do not fail over.
- `PurchaseResult::servedBy` is recorded to `transactions.provider_id` and the attempt row.
- A failover target whose cost is at or above the selling price is skipped or alerted.
- Each attempt uses a separate `attempt_reference` unless the provider treats our reference as an idempotency key.

If the existing `FailoverProvider` does not already do one of these, record it as a gap in section 15 and wrap it with a decorator (`GuardedFailover`) rather than editing it.

## 11. Finalize, refund, requery, webhooks

### `FinalizeTransaction` (idempotent)
- Lock the transaction row.
- If the status is already final (`success`, `failed`, `refunded`), return.
- `success`: store `provider_reference`, `token`, `completed_at`, compute `profit`.
- `failed`: mark failed, call `RefundTransaction`.
- `pending`: keep as is, update `provider_response`.

### `RefundTransaction`
- Inserts a `refund` ledger row. The unique `(transaction_id, type)` index makes a second refund impossible.
- Sets the transaction to `refunded` or `failed` per your wording; pick one and use it everywhere.

### `RequeryPending` (scheduled every 1–2 minutes)
- Selects `pending` transactions older than 1 minute.
- For each, calls the matching `StatusChecker` using the **served attempt's** provider and `attempt_reference`.
- Passes the result to `FinalizeTransaction`.
- After N hours of `pending`, alert an admin. Do not auto-refund.

### Webhooks
One controller per route `POST /webhooks/vtu/{slug}`:
1. Resolve `WebhookHandler` by slug, `verify()` the signature.
2. `parse()` into `PurchaseResult` plus the attempt reference.
3. `FinalizeTransaction`.
4. Always return 200 quickly, and process by queue if heavy.

## 12. Folder structure (additions only)

```
app/Vtu/
├── Contracts/        PlanSource, ResultMapper, StatusChecker,
│                     WebhookHandler, ValidatesRecipient
├── Data/             PlanData, PurchaseData, PurchaseResult, SyncDiff, RecipientInfo
├── Actions/          PreviewPlanSync, ApplyPlanSync, PurchasePlan,
│                     FinalizeTransaction, RefundTransaction, RequeryPending
├── Jobs/             SyncProviderPlans, ProcessPurchaseJob, RequeryPendingJob
├── Models/           Product, ProviderPlan, SyncPreview, Transaction,
│                     TransactionAttempt
├── Providers/Epins/  EpinsPlanSource, EpinsResultMapper,
│                     EpinsStatusChecker, EpinsWebhookHandler
├── Support/          ProviderSidecars (registry), PricingService
├── Http/             VtuWebhookController, PurchaseController
└── Filament/         PlanSyncPage, ProviderPlanResource, TransactionResource
```

## 13. Security and integrity

- Provider credentials in the connector config, encrypted at rest, never logged.
- Webhook signature verification is mandatory, with replay protection where the provider supports it.
- Rate limit the purchase endpoint per user and per recipient.
- Wallet operations only through the ledger, always under row locks.
- Idempotency key on the customer purchase request, to stop double-tap duplicates.
- Store raw request and response for disputes. Redact secrets before storing.

## 14. Testing

- `normalizePlans()` and `ResultMapper` are pure, so unit test them with saved provider fixtures (success, failed, processing, malformed, empty).
- Feature tests for `PurchasePlan`: insufficient balance, margin guard, concurrent double-spend, and idempotency key.
- `FinalizeTransaction`: double webhook, webhook plus requery race, refund twice.
- Failover scenarios using fake providers: failed then success, pending stops chain, all failed.
- `ApplyPlanSync`: expired preview, empty payload, double apply, price jump.

## 15. Open questions

1. Does the existing `FailoverProvider` already swap the plan per provider, and where does it get the equivalent plan from? This decides whether `products` plus `provider_plans` is needed.
2. Does `EpinsProvider` already normalize results and expose requery? If so, the corresponding sidecars become thin wrappers.
3. Which product types are in scope for v1 (airtime and data only, or cable and electricity too)?
4. Does each provider support webhooks, or is requery the only path?
5. Does the client need agent and reseller price levels in v1?

## 16. Implementation order

1. Migrations: `products`, `provider_plans`, `sync_previews`, `transactions`, `transaction_attempts`, wallet ledger.
2. DTOs, contracts, `ProviderSidecars` registry.
3. Epins sidecars with fixtures and unit tests.
4. `PricingService`.
5. `PreviewPlanSync`, `ApplyPlanSync`, and the Filament sync page.
6. `PurchasePlan`, `ProcessPurchaseJob`, `FinalizeTransaction`, `RefundTransaction`.
7. `RequeryPending` and the webhook controller.
8. Second provider's sidecars and failover tests.
