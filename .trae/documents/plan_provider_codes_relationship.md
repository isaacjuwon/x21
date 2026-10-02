# Multi-Provider Plan Codes (Relationship + Repeater) Implementation Plan

> **Key Directives (all three)**
> 1. **Only `api_code` as the public concept** on plans — no separate `vtpass_code` field/accessor.
> 2. **Failover order reads `config/api.php`** (`failover.providers` list) — same source of truth used by `FailoverVtuProvider`.
> 3. **Epins provider is being removed and replaced by `vtugate.com`** → plan-level `api_code` column values (which held Epins-specific codes) are discarded. Only existing `vtpass_code` values are worth salvaging into the relationship. Legacy columns stay in Phase 1 (safe rollback) but their data is treated as dead for plans.

---

## Repository Research

### Current Architecture

Two layers of failover exist today:

1. **Provider-level failover** — [FailoverVtuProvider.php](file:///c:/isaac/ship/app/Integrations/Failover/FailoverVtuProvider.php#L24-L256)
   - Reads order from [config/api.php](file:///c:/isaac/ship/config/api.php#L48-L56): currently `['epins', 'vtpass']`.
   - Each try passes the **same entity** (carrying all provider codes) to the next provider.

2. **Code-level behaviour inside providers** (entity transport fields):
   - Epins → reads `PurchaseData->dataCode` (the old `$plan->api_code`)
   - VTPass → reads `PurchaseData->vtpassCode ?: PurchaseData->dataCode` fallback

*Important context*: Entity DTO field names (`dataCode`, `vtpassCode`) are transport-layer names for provider API payloads. They stay unchanged; this refactor only changes the model layer *source* of those strings.

### Impact of the Epins → Vtugate swap

- Values currently stored in `data_plans.api_code`, `cable_plans.api_code`, `education_plans.api_code`, `electricity_plans.api_code`, `airtime_plans.api_code` are **Epins-specific plan codes** — they do NOT work with vtugate.com → DO NOT migrate them (treat as dead data).
- Values in `*_plans.vtpass_code` still work → migrate those as provider = 'vtpass' rows in the new relationship.
- `Brand.api_code` is a *different semantic*: network/service identifiers (e.g. `'mtn'`, `'ikeja-electric'`), **not** an Epins plan code → Brand stays untouched in Phase 1; its `api_code` usage is reviewed separately.
- The failover chain should conceptually become `['vtugate', 'vtpass']` in config; this plan's code references the NEW chain via `config('api.providers.failover.providers')`, not hardcoded provider names.

### Root problem

Six tables use flat columns `api_code` + `vtpass_code`. Add one provider → 6 ALTERs + 6 form rewrites = N+1 columns anti-pattern. We fix it and handle the Epins→Vtugate data clean-break in one move.

---

## Design Recommendation

### Polymorphic relationship `plan_provider_codes`

```
plan_provider_codes table
┌────────────────┬──────────────────────────────────────────┐
│ id             │ PK                                       │
│ planable_type  │ morphs (DataPlan/CablePlan/…/Brand)      │
│ planable_id    │ FK unsignedBigInt                        │
│ provider       │ string  ─ matches failover.providers keys│
│ code           │ string  ─ provider-specific plan code    │
│ (no priority column) ─ ordering is config-driven          │
│ created_at / updated_at                                    │
└────────────────┴──────────────────────────────────────────┘
  UNIQUE (planable_type, planable_id, provider)
  INDEX  (planable_type, planable_id)
```

No `priority` column. Fallback walks `config('api.providers.failover.providers')` in its array order = **exactly** the order `FailoverVtuProvider` tries providers. One source of truth.

### Public contract on plan models

```php
// —— Primary backwards-compatible-ish property ——
$plan->api_code
//   NOW RETURNS: resolveApiCode() for the FIRST provider in the
//   NEW config failover chain (= 'vtugate').
//   NOTE: legacy api_code DB column values are NOT used (Epins dead data).
//   This accessor exists so call sites don't throw; callers get
//   Vtugate's code (once entered in admin) via the relationship.
//   For plans with no relationship rows it returns null (correct,
//   because epins data is dead, not silently reused).

// —— Exact per-provider lookup, no fallback ——
$plan->apiCodeFor('vtugate'): ?string
$plan->apiCodeFor('vtpass'): ?string

// —— Config-driven resolver with failover ——
$plan->resolveApiCode(?string $preferredProvider = null): ?string
//
//   1. exact match apiCodeFor($preferredProvider) if given
//   2. else walk config('failover.providers') in order, return first hit
//   3. else null  (NO legacy api_code column fallback for plans —
//                  those were Epins codes and are invalid for Vtugate)

// —— Relation ——
$plan->providerCodes: Collection<PlanProviderCode>
```

**"Only `api_code` public concept" consequences**:
- No `getVtpassCodeAttribute()` accessor anywhere.
- Filament form has no "VTPass Variation Code" field.
- Filament table has no "VTPass Variation Code" column.
- Exactly ONE Filament Repeater widget per plan: **"API Codes"** → rows of `[provider Select | code TextInput]`.

### Action layer contract

Actions read ALL codes via `apiCodeFor` / `resolveApiCode`. NEVER via `$plan->vtpass_code` property.

```php
// Data purchase — same pattern for cable/education/electricity
new PurchaseDataEntity(
    network:      (string) ($transaction->meta['network'] ?? $transaction->brand->api_code),
    mobileNumber: (string) $transaction->recipient,
    dataCode:     (string) $transaction->plan->resolveApiCode('vtugate'), // primary
    reference:    $transaction->reference,
    vtpassCode:   $transaction->plan->apiCodeFor('vtpass'),               // VTPass own field (null ok)
);
```

Note: `dataCode` (entity DTO field) used to be the Epins plan code. After the provider swap, the NEW primary provider Vtugate likely uses the same field naming convention as Epins (that's the typical pattern when swapping providers behind a common entity contract). If Vtugate's payload naming differs, that adjustment belongs in the Vtugate connector/resource layer — not in this plan-code storage refactor. This plan's scope ends at populating the entity DTO correctly.

---

## Scope (Phased)

### Phase 1 (this plan) — DataPlan only as pilot
Migration → trait → model → Filament form/table → PurchaseDataAction + seed (only vtpass codes) + tests

### Phase 2 (separate plan)
Same `HasProviderCodes` trait applied to CablePlan, EducationPlan, ElectricityPlan, AirtimePlan, Brand + Filament edits + seeders for each.

### Also needed (separate/config change, not in code of this plan)
- `config/api.php` updates: register `vtugate` provider driver, remove epins from `failover.providers` list, add `vtugate` at the head of the list (documented here as dependency)
- Build `App\Integrations\Vtugate\VtugateProvider` + connector/resources (out of scope for this storage refactor)

---

## Files and Modules

### New files
- `database/migrations/<timestamp>_create_plan_provider_codes_table.php` — polymorphic table
- `app/Models/PlanProviderCode.php` — MorphTo model (fillable: `provider`, `code`)
- `app/Models/Concerns/HasProviderCodes.php` — Trait: `providerCodes()`, `apiCodeFor()`, `resolveApiCode()`, `getApiCodeAttribute()`
- `database/seeders/MigrateLegacyDataPlanVtpassCodesSeeder.php` — ONE-WAY seeder: migrates ONLY existing `vtpass_code` → `provider = 'vtpass'` rows. SKIPS legacy `api_code` (Epins dead codes). Idempotent via `firstOrCreate`.

### Modified files (Phase 1 — DataPlan)
- [DataPlan.php](file:///c:/isaac/ship/app/Models/DataPlan.php#L1-L29) — `use HasProviderCodes;` + `$with = ['providerCodes']`. Legacy fillable still lists columns (for DB schema compatibility), but they are no longer written/read intentionally.
- [DataPlanForm.php](file:///c:/isaac/ship/app/Filament/Clusters/Plans/Resources/DataPlans/Schemas/DataPlanForm.php#L1-L71)
  - REMOVE both TextInputs: `api_code` ("Default API Code (Epins)") + `vtpass_code` ("VTPass Variation Code")
  - ADD one `Repeater::make('providerCodes')->relationship()` labelled "API Codes"
- [DataPlansTable.php](file:///c:/isaac/ship/app/Filament/Clusters/Plans/Resources/DataPlans/Tables/DataPlansTable.php)
  - REMOVE both TextColumns: `TextColumn::make('api_code')` / `TextColumn::make('vtpass_code')`
  - ADD one computed `TextColumn` summarising per-plan provider:code pairs
- [PurchaseDataAction.php](file:///c:/isaac/ship/app/Actions/Vtu/PurchaseDataAction.php#L21-L66)
  - Line 26 `dataCode`: `resolveApiCode('vtugate')` (was `api_code`)
  - Line 28 `vtpassCode`: `apiCodeFor('vtpass')` (was `vtpass_code`)
  - Add guard: if primary resolves to null → RuntimeException with error message naming the failover chain
- (Optional, read-only) [ServicePlanResource.php](file:///c:/isaac/ship/app/Http/Resources/Api/V1/Services/ServicePlanResource.php#L17) — `api_code` key now served via the new accessor; no code change strictly required.

---

## Implementation Steps (dependency order)

### 1. Migration `create_plan_provider_codes_table`

```php
Schema::create('plan_provider_codes', function (Blueprint $table) {
    $table->id();
    $table->morphs('planable');
    $table->string('provider');
    $table->string('code');
    $table->timestamps();
    $table->unique(['planable_type', 'planable_id', 'provider']);
});
```

No `priority` column.

### 2. `PlanProviderCode` model

```php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PlanProviderCode extends Model
{
    protected $fillable = ['provider', 'code'];

    public function planable(): MorphTo { return $this->morphTo(); }
}
```

### 3. `HasProviderCodes` trait

```php
namespace App\Models\Concerns;

use App\Models\PlanProviderCode;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasProviderCodes
{
    public function providerCodes(): MorphMany
    {
        return $this->morphMany(PlanProviderCode::class, 'planable');
    }

    public function apiCodeFor(string $provider): ?string
    {
        return $this->providerCodes->firstWhere('provider', $provider)?->code;
    }

    /** @return list<string> ordered providers from config */
    protected function failoverProviderChain(): array
    {
        return config('api.providers.failover.providers', ['vtugate']);
    }

    public function resolveApiCode(?string $preferredProvider = null): ?string
    {
        $chain = $this->failoverProviderChain();

        $candidates = [];
        if ($preferredProvider !== null) {
            $candidates[] = $preferredProvider;
        }
        foreach ($chain as $p) {
            if (! in_array($p, $candidates, true)) {
                $candidates[] = $p;
            }
        }

        foreach ($candidates as $provider) {
            if ($code = $this->apiCodeFor($provider)) {
                return $code;
            }
        }

        // INTENTIONALLY NO legacy api_code column fallback.
        // Those values are Epins codes; reusing them silently against
        // Vtugate would corrupt transactions. Return null, let actions
        // throw the "no resolvable code" guard below.
        return null;
    }

    public function getApiCodeAttribute(): ?string
    {
        // Default = first provider in the configured failover chain.
        // Today that first provider = vtugate after the swap.
        $head = $this->failoverProviderChain()[0] ?? 'vtugate';

        return $this->resolveApiCode($head);
    }
}
```

### 4. DataPlan model

```php
use App\Models\Concerns\HasProviderCodes;

class DataPlan extends Model
{
    use HasProviderCodes;

    protected $with = ['providerCodes'];

    // Legacy columns still present in DB schema / fillable for safety in Phase 1.
    // Their values are Epins dead data; not read by any code in this path.
    protected $fillable = ['name', 'brand_id', 'type', 'api_code', 'vtpass_code',
                           'price', 'duration', 'status'];
    protected $casts    = ['price' => 'decimal:2', 'status' => 'boolean'];

    public function brand() { return $this->belongsTo(Brand::class); }
}
```

### 5. DataPlanForm (Filament)

Delete the two existing TextInputs. In their place (still inside the Plan Details Section, replacing the 3-col Grid):

```php
Section::make('API Codes')
    ->schema([
        Repeater::make('providerCodes')
            ->label('Provider API Codes')
            ->relationship()
            ->schema([
                Select::make('provider')
                    ->options(
                        collect(config('api.providers.failover.providers', ['vtugate']))
                            ->mapWithKeys(fn (string $p) => [$p => ucfirst($p)])
                            ->all()
                    )
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Provider'),
                TextInput::make('code')
                    ->required()
                    ->placeholder('e.g. VTG_MTN_1GB  or  mtn-1gb-1000'),
            ])
            ->columns(2)
            ->minItems(1)
            ->addActionLabel('Add API code')
            ->itemLabel(
                fn (array $state): ?string => isset($state['provider'])
                    ? strtoupper($state['provider'])
                    : null
            ),
    ]),
```

Notice: The Select options are auto-built from the config failover list. Adding future providers to config → they appear here automatically.

### 6. DataPlansTable (Filament list)

Replace the two TextColumns with one summary column. Legacy api_code search is intentionally dropped (Epins codes are dead data); only relationship codes + vtpass legacy are searchable via migrated rows.

```php
TextColumn::make('api_codes_summary')
    ->label('API Codes')
    ->state(function (DataPlan $record): string {
        $pairs = $record->providerCodes
            ->map(fn ($c) => "{$c->provider}: {$c->code}")
            ->join('  ·  ');

        return $pairs ?: '—';
    })
    ->searchable(
        query: fn ($q, string $s) => $q
            ->whereHas('providerCodes', fn ($q2) => $q2->where('code', 'like', "%{$s}%")),
    ),
```

### 7. PurchaseDataAction

```php
public function handle(TopupTransaction $transaction): ServiceResponse
{
    $primaryProvider = config('api.providers.failover.providers.0', 'vtugate');
    $primaryCode = $transaction->plan->resolveApiCode($primaryProvider);

    if ($primaryCode === null) {
        throw new \RuntimeException(
            "DataPlan #{$transaction->plan->id} has no API code resolvable for "
            ."primary provider [{$primaryProvider}]. Configured failover chain: "
            .json_encode(config('api.providers.failover.providers')).'. '
            .'Add provider API codes for this plan in admin.'
        );
    }

    $entity = new PurchaseDataEntity(
        network:      (string) ($transaction->meta['network'] ?? $transaction->brand->api_code),
        mobileNumber: (string) $transaction->recipient,
        dataCode:     (string) $primaryCode,
        reference:    $transaction->reference,
        vtpassCode:   $transaction->plan->apiCodeFor('vtpass'),
    );

    // … the rest of handle() unchanged (try/catch + RecordApiRequestJob + events)
}
```

### 8. One-way legacy VTPass code seeder for DataPlan

Renamed to reflect we're NOT salvaging Epins `api_code` data:

```php
// database/seeders/MigrateLegacyDataPlanVtpassCodesSeeder.php
use App\Models\DataPlan;
use Illuminate\Database\Seeder;

class MigrateLegacyDataPlanVtpassCodesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DataPlan::cursor() as $plan) {
            // --- INTENTIONALLY SKIPPED: $plan->api_code (Epins dead data) ---

            $vtp = $plan->getRawOriginal('vtpass_code');
            if (! empty($vtp)) {
                $plan->providerCodes()->firstOrCreate(
                    ['provider' => 'vtpass'],
                    ['code' => $vtp],
                );
            }
        }
    }
}
```

`firstOrCreate` → idempotent (safe to re-run after admins have started editing).

### 9. Pest tests

**Unit, trait (`tests/Unit/Models/Concerns/HasProviderCodesTest.php`)**
- `it returns exact api code for a provider via apiCodeFor`
- `resolveApiCode walks configured failover.providers in declared order`
  - Set `config(['api.providers.failover.providers' => ['vtugate', 'vtpass']])`; seed only `vtpass` row → `resolveApiCode('vtugate')` returns the vtpass row's code (fallback works)
- `resolveApiCode returns null when no provider codes exist (no legacy epins reuse)` — ensures no silent Epins→Vtugate cross-contamination
- `$plan->api_code returns resolveApiCode for head of the failover chain` — alias semantics

**Feature, Filament (`tests/Feature/Filament/DataPlanProviderCodesTest.php`)**
- Create DataPlan with 2 Repeater rows (vtugate + vtpass) → both stored; `apiCodeFor('vtugate')` matches input
- Edit DataPlan → delete vtpass row, change vtugate code → 1 row left, value updated

**Feature, PurchaseDataAction (`tests/Feature/Actions/Vtu/PurchaseDataActionProviderCodesTest.php`)**
- Given a DataPlan with vtugate code only (via relationship, both legacy columns null) → action builds entity with correct dataCode, calls provider's purchaseData, passes `apiCodeFor('vtpass')` = null as vtpassCode
- Given a DataPlan with no rows at all → action throws the RuntimeException with resolvable-code message
- Given a DataPlan with only vtpass code (vtugate row absent) → resolveApiCode walks the chain and returns the vtpass code (failover works end-to-end)

### 10. Format, lint, test

```
vendor/bin/pint --dirty --format agent
php artisan test --compact
php -l on every new/modified file
```

---

## Dependencies and Considerations

- **Epins→Vtugate data clean break is explicit and intentional.** Old plan api_code values are dead; this plan makes NO attempt to reuse them against Vtugate (that would be wrong). Admins will need to enter vtugate codes for each plan.
- **`$plan->api_code` semantics move with the failover chain head.** Today that head will be vtugate; 49 existing callers of `$x->api_code` get the new correct value once vtugate codes are in the relationship. Plans without a vtugate code yield `null` (better than silently wrong Epins codes).
- **`Brand.api_code` is NOT a plan code and is unchanged in Phase 1.** Brand's `api_code` stores things like `'mtn'`/`'glo'`/`'ikeja-electric'` — those are provider-neutral network/service IDs and are not covered by the Epins removal.
- **Failover chain is the single source of truth.** Adding provider #3:
  1. Add it once to `failover.providers` in config.
  2. Filament Repeater Select auto-offers the new option.
  3. `resolveApiCode` auto-walks the expanded list.
- **Migration is reversible (lose new relationship rows only).** `down()` drops `plan_provider_codes`. Legacy columns on plans are NOT touched → rollback restores pre-refactor state at the cost of losing admin edits made via the Repeater (acceptable for Phase 1 pilot).
- **Entity DTO and provider implementations stay unchanged.** This plan only adjusts where code strings are sourced from, not how they're transmitted.

---

## Validation

1. `php artisan migrate` → `plan_provider_codes` table created
2. `php artisan db:seed --class=MigrateLegacyDataPlanVtpassCodesSeeder` → plans that had a `vtpass_code` now have 1 row with provider = 'vtpass'
3. Tinker check:
   ```
   $p = App\Models\DataPlan::first(fn($q) => $q->whereNotNull('vtpass_code'));
   echo $p->apiCodeFor('vtpass') === $p->getRawOriginal('vtpass_code') ? 'OK' : 'FAIL';
   ```
4. Filament admin:
   - Create new DataPlan → 2 Repeater rows (vtugate + vtpass) → Save → reload → Edit → rows present → delete vtpass → save → only vtugate remains
   - `$plan->api_code` tinker check returns the vtugate code entered
   - Summary column shows `vtugate: VTG_X · vtpass: mtn-y`
   - Search finds plans by any provider code value
5. `php artisan test --compact --filter=ProviderCode` → green
6. `php artisan test --compact --filter=DataPlan` → green (existing tests)
7. `php artisan test --compact --filter=PurchaseData` → green (action tests)
8. `vendor/bin/pint --dirty --format agent` → no diffs
9. Syntax check on all new/modified files

---

## Risks

| Risk | Impact | Mitigation |
|---|---|---|
| Plans without vtugate codes → `null` api_code → 49 callers encounter nullable values where they expected non-empty | High | PurchaseDataAction has an explicit guard with clear error message. Phase 1 pilot scope = DataPlan only; admins re-enter vtugate codes for DataPlans before rolling out. Trait unit test asserts no accidental reuse of legacy epins column. |
| Filament Repeater `->relationship()` with MorphMany fails to auto-save | Medium | Feature test catches; fallback to manual `afterCreate()`/`afterUpdate()` hooks on `CreateDataPlan`/`EditDataPlan` pages |
| Unique constraint fires when admin picks the same provider twice | Low | Form-level `Select::unique(ignoreRecord: true)` + DB constraint double guard |
| Config `failover.providers` key absent in new env | Low | All `config()` calls default to `['vtugate']` |
| Seeder runs before models/trait autoloaded in same batch | Low | Run seeder separately after migration batch completes (documented) |
| `$with = ['providerCodes']` adds load in unforeseen paths | Low | ≤2 rows/plan; eliminates N+1 on listing and on any caller that touches `$plan->api_code` (accessor reads from `$this->providerCodes` collection) |
| Brand.api_code semantics confused with plan.api_code (network IDs vs plan codes) | Medium | Brand deferred entirely to Phase 2 + documented that Brand's usage is network/service IDs not plan codes |
| Vtugate payload naming != Epins payload naming → `dataCode` DTO field needs rename | Low (out of scope) | Handled in the separate Vtugate connector/integration build; the scope of THIS storage refactor ends at populating the entity DTO from relationship rows |
