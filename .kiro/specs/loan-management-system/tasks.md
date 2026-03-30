# Implementation Plan: Loan Management System

## Overview

Implement a backend loan management system on Laravel 13 / PHP 8.4 with SQLite and Pest v4. Tasks are ordered by dependency: migrations → enums/config → models → interfaces/specifications → eligibility → exceptions → actions → events/jobs/notifications → form requests → resources → controllers → routes → tests.

## Tasks

- [x] 1. Database migrations
  - [x] 1.1 Create the `loans` table migration
    - Run `php artisan make:migration create_loans_table`
    - Columns: `id`, `user_id` (FK→users), `principal_amount` decimal(15,2), `outstanding_balance` decimal(15,2), `interest_rate` decimal(5,4), `repayment_term_months` unsignedInteger, `interest_method` string default `FlatRate`, `status` string default `active`, `disbursed_at` nullable timestamp, `eligibility_checked_at` nullable timestamp, `eligibility_passed` nullable boolean, `notes` nullable text, `rejection_reason` nullable text, timestamps
    - _Requirements: 1.1, 14.1, 17.3, 17.4_

  - [x] 1.2 Create the `loan_schedule_entries` table migration
    - Run `php artisan make:migration create_loan_schedule_entries_table`
    - Columns: `id`, `loan_id` (FK→loans cascade delete), `instalment_number` unsignedInteger, `due_date` date, `instalment_amount` decimal(15,2), `principal_component` decimal(15,2), `interest_component` decimal(15,2), `outstanding_balance` decimal(15,2), `status` string default `Pending`, `remaining_amount` decimal(15,2), `paid_at` nullable timestamp, timestamps
    - _Requirements: 6.2, 15.1, 16.1_

  - [x] 1.3 Create the `loan_repayments` table migration
    - Run `php artisan make:migration create_loan_repayments_table`
    - Columns: `id`, `loan_id` (FK→loans cascade delete), `amount` decimal(15,2), `transaction_id` nullable bigint (FK→transactions), timestamps
    - _Requirements: 7.2_

  - [x] 1.4 Create the `loan_status_histories` table migration
    - Run `php artisan make:migration create_loan_status_histories_table`
    - Columns: `id`, `loan_id` (FK→loans cascade delete), `from_status` nullable string, `to_status` string, `actor_user_id` (FK→users), `notes` nullable text, `created_at` timestamp only (no `updated_at`)
    - _Requirements: 17.1_

- [x] 2. Enums and configuration
  - [x] 2.1 Create the three loan enums under `App\Enums\Loans\`
    - Run `php artisan make:enum Enums/Loans/LoanStatus` — cases: `Active='active'`, `Approved='approved'`, `Disbursed='disbursed'`, `Rejected='rejected'`
    - Run `php artisan make:enum Enums/Loans/InterestMethod` — cases: `FlatRate='FlatRate'`, `ReducingBalance='ReducingBalance'`
    - Run `php artisan make:enum Enums/Loans/LoanScheduleEntryStatus` — cases: `Pending='Pending'`, `Paid='Paid'`, `Overdue='Overdue'`
    - _Requirements: 14.1, 15.1_

  - [x] 2.2 Create `config/loans.php`
    - Keys: `min_account_age_days` (integer, e.g. 30), `levels` (array mapping level→max_amount, e.g. `[1 => 5000, 2 => 15000, 3 => 50000]`)
    - _Requirements: 2.5, 2.6_

- [x] 3. Eloquent models and factories
  - [x] 3.1 Create the `Loan` model
    - Run `php artisan make:model Loan --factory`
    - Add `$fillable` for all loan columns; cast `status` to `LoanStatus`, `interest_method` to `InterestMethod`
    - Relationships: `belongsTo(User::class)`, `hasMany(LoanScheduleEntry::class)`, `hasMany(LoanRepayment::class)`, `hasMany(LoanStatusHistory::class)`
    - Factory: generate realistic `principal_amount`, `interest_rate`, `repayment_term_months`, default `status = LoanStatus::Active`; add states `approved()`, `disbursed()`, `rejected()`
    - _Requirements: 1.1, 1.5_

  - [x] 3.2 Create the `LoanScheduleEntry` model
    - Run `php artisan make:model LoanScheduleEntry --factory`
    - Cast `status` to `LoanScheduleEntryStatus`; `belongsTo(Loan::class)`
    - Factory: generate valid entry data with `status = LoanScheduleEntryStatus::Pending`
    - _Requirements: 6.2, 15.1_

  - [x] 3.3 Create the `LoanRepayment` model
    - Run `php artisan make:model LoanRepayment --factory`
    - `$fillable`: `loan_id`, `amount`, `transaction_id`; `belongsTo(Loan::class)`
    - _Requirements: 7.2_

  - [x] 3.4 Create the `LoanStatusHistory` model
    - Run `php artisan make:model LoanStatusHistory`
    - Set `$timestamps = false` and add `const CREATED_AT = 'created_at'`; `$fillable`: all columns; `belongsTo(Loan::class)`, `belongsTo(User::class, 'actor_user_id')`
    - _Requirements: 17.1_

  - [x] 3.5 Add `HasWallets` trait to the `User` model
    - Edit `app/Models/User.php` to use `App\Concerns\HasWallets`
    - _Requirements: 4.2, 7.1_


- [x] 4. LoanSpecification interface and implementations
  - [x] 4.1 Create the `LoanSpecification` interface under `App\Loans\Specifications\`
    - Run `php artisan make:interface Loans/Specifications/LoanSpecification`
    - Methods: `isSatisfiedBy(User $user): bool` and `failureReason(): string`
    - _Requirements: 2.1_

  - [x] 4.2 Create `UserDurationSpecification`
    - Run `php artisan make:class Loans/Specifications/UserDurationSpecification`
    - Implements `LoanSpecification`; reads `config('loans.min_account_age_days')`; passes when `now()->diffInDays($user->created_at) >= threshold`
    - _Requirements: 2.5_

  - [x] 4.3 Create `LoanLevelSpecification`
    - Run `php artisan make:class Loans/Specifications/LoanLevelSpecification`
    - Implements `LoanSpecification`; accepts requested amount via constructor; reads `config('loans.levels')`; passes when amount ≤ level limit for the user's loan level (default level 1 if no level attribute exists)
    - _Requirements: 2.6_

- [x] 5. LoanEligibilityChecker and EligibilityResult
  - [x] 5.1 Create `EligibilityResult` value object under `App\Loans\`
    - Run `php artisan make:class Loans/EligibilityResult`
    - Properties: `bool $passed`, `?LoanSpecification $failingSpecification`; readonly constructor
    - _Requirements: 2.3, 2.4_

  - [x] 5.2 Create `LoanEligibilityChecker` under `App\Loans\`
    - Run `php artisan make:class Loans/LoanEligibilityChecker`
    - Constructor accepts `array $specifications`; `check(User $user): EligibilityResult` iterates specs and returns first failure or a passing result
    - _Requirements: 2.2, 2.3, 2.4_

  - [x] 5.3 Register specifications and `LoanEligibilityChecker` in `AppServiceProvider`
    - Bind `LoanEligibilityChecker` in `register()` with `UserDurationSpecification` and `LoanLevelSpecification` injected as the specifications array
    - Register the `loan-applications` rate limiter in `boot()` using `RateLimiter::for('loan-applications', ...)`
    - _Requirements: 2.2, 2.7, 12.1_

- [x] 6. Exceptions
  - [x] 6.1 Create `InvalidLoanStateException` under `App\Exceptions\Loans\`
    - Run `php artisan make:exception Exceptions/Loans/InvalidLoanStateException`
    - Extends `\RuntimeException`
    - _Requirements: 3.2, 4.4, 5.3_

  - [x] 6.2 Create `LoanIneligibleException` under `App\Exceptions\Loans\`
    - Run `php artisan make:exception Exceptions/Loans/LoanIneligibleException`
    - Accepts a `LoanSpecification` in constructor; exposes `getFailingSpecification(): LoanSpecification`
    - _Requirements: 13.2_

  - [x] 6.3 Register exception renderers in `bootstrap/app.php`
    - Add `->withExceptions()` handlers for `InvalidLoanStateException` → 422 JSON, `LoanIneligibleException` → 422 JSON with failing reason, `InsufficientFundsException` → 422 JSON
    - _Requirements: 3.2, 4.4, 5.3, 7.5, 13.2_

- [x] 7. Events
  - [x] 7.1 Create loan lifecycle events
    - Run `php artisan make:event Events/Loans/LoanApproved` — constructor accepts `Loan $loan`
    - Run `php artisan make:event Events/Loans/LoanDisbursed` — constructor accepts `Loan $loan`
    - Run `php artisan make:event Events/Loans/LoanRejected` — constructor accepts `Loan $loan`
    - Run `php artisan make:event Events/Loans/LoanSettled` — constructor accepts `Loan $loan`
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [x] 8. Notifications
  - [x] 8.1 Create queueable loan notifications under `App\Notifications\Loans\`
    - Run `php artisan make:notification Notifications/Loans/LoanApprovedNotification`
    - Run `php artisan make:notification Notifications/Loans/LoanDisbursedNotification`
    - Run `php artisan make:notification Notifications/Loans/LoanRejectedNotification`
    - Run `php artisan make:notification Notifications/Loans/LoanSettledNotification`
    - Each must implement `ShouldQueue`, use `Queueable`, accept `Loan $loan` in constructor, and implement `via()` returning `['database']` (or `['mail']` as appropriate)
    - _Requirements: 18.1, 18.2, 18.3, 18.4_

- [x] 9. Queued job
  - [x] 9.1 Create `GenerateLoanScheduleJob` under `App\Jobs\`
    - Run `php artisan make:job Jobs/GenerateLoanScheduleJob`
    - Accepts `Loan $loan`; `handle()` calls `GenerateLoanScheduleAction`
    - _Requirements: 10.5_

- [x] 10. Action classes under `App\Actions\Loans\`
  - [x] 10.1 Create `CheckLoanEligibilityAction`
    - Run `php artisan make:class Actions/Loans/CheckLoanEligibilityAction`
    - Inject `LoanEligibilityChecker`; `handle(User $user, float $amount): EligibilityResult`; throws `LoanIneligibleException` on failure
    - _Requirements: 13.1, 13.2_

  - [x] 10.2 Create `GenerateLoanScheduleAction`
    - Run `php artisan make:class Actions/Loans/GenerateLoanScheduleAction`
    - `handle(Loan $loan): void`; branches on `$loan->interest_method`; bulk-inserts `LoanScheduleEntry` records with `status = Pending` and `remaining_amount = instalment_amount`
    - Flat-rate formula: `total_interest = P × R × (T/12)`, `instalment = (P + total_interest) / T`
    - Reducing-balance formula: `monthly_rate = R/12`, `instalment = P × monthly_rate / (1 - (1+monthly_rate)^(-T))`
    - _Requirements: 6.1, 6.2, 6.5, 14.2, 14.3_

  - [x] 10.3 Create `ApproveLoanAction`
    - Run `php artisan make:class Actions/Loans/ApproveLoanAction`
    - `handle(Loan $loan, User $actor, ?string $notes = null): Loan`; throws `InvalidLoanStateException` if status ≠ `Active`; transitions to `Approved`; inserts `LoanStatusHistory`; fires `LoanApproved`; dispatches `LoanApprovedNotification` to loan user
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 17.1, 17.2, 17.3_

  - [x] 10.4 Create `DisburseLoanAction`
    - Run `php artisan make:class Actions/Loans/DisburseLoanAction`
    - `handle(Loan $loan, User $actor, ?string $notes = null): Loan`; throws `InvalidLoanStateException` if status ≠ `Approved`; calls `$loan->user->deposit(principal, WalletType::General)`; sets `disbursed_at`; transitions to `Disbursed`; inserts `LoanStatusHistory`; fires `LoanDisbursed`; dispatches `GenerateLoanScheduleJob`; dispatches `LoanDisbursedNotification`
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 17.1, 17.2, 17.3_

  - [x] 10.5 Create `RejectLoanAction`
    - Run `php artisan make:class Actions/Loans/RejectLoanAction`
    - `handle(Loan $loan, User $actor, string $rejectionReason, ?string $notes = null): Loan`; throws `InvalidLoanStateException` if status is `Disbursed` or `Rejected`; stores `rejection_reason`; transitions to `Rejected`; inserts `LoanStatusHistory`; fires `LoanRejected`; dispatches `LoanRejectedNotification`
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 17.1, 17.2, 17.4_

  - [x] 10.6 Create `RepayLoanAction`
    - Run `php artisan make:class Actions/Loans/RepayLoanAction`
    - `handle(Loan $loan, User $actor, float $amount): LoanRepayment`; throws `InvalidLoanStateException` if status ≠ `Disbursed`; throws 422 if `$amount > $loan->outstanding_balance`; calls `$loan->user->withdraw(amount, WalletType::General)`; creates `LoanRepayment`; decrements `outstanding_balance`; reduces `remaining_amount` on the earliest `Pending` entry, marks it `Paid` (sets `paid_at`) when `remaining_amount` reaches 0; fires `LoanSettled` + dispatches `LoanSettledNotification` if `outstanding_balance` reaches 0
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 16.1, 16.2, 16.3_

- [x] 11. LoanPolicy
  - [x] 11.1 Create `LoanPolicy` under `App\Policies\`
    - Run `php artisan make:policy LoanPolicy --model=Loan`
    - `viewAny`: authenticated users only; `create`: authenticated users only; `approve`, `disburse`, `reject`: admin-only (e.g. check `$user->is_admin` or a role — use a simple boolean attribute for now, consistent with existing User model)
    - _Requirements: 11.1, 11.2, 11.3, 11.4_


- [x] 12. Form requests
  - [x] 12.1 Create `StoreLoanRequest`
    - Run `php artisan make:request Http/Requests/Api/V1/StoreLoanRequest`
    - Rules: `principal_amount` (required, numeric, min:1), `repayment_term_months` (required, integer, min:1), `interest_method` (sometimes, in:FlatRate,ReducingBalance, defaults to FlatRate)
    - In `passesAuthorization()` or `after()` hook, call `CheckLoanEligibilityAction` and throw `LoanIneligibleException` on failure
    - _Requirements: 1.3, 1.4, 9.3, 13.1, 13.2_

  - [x] 12.2 Create `StoreRepaymentRequest`
    - Run `php artisan make:request Http/Requests/Api/V1/StoreRepaymentRequest`
    - Rules: `amount` (required, numeric, min:0.01); max validated against `$this->route('loan')->outstanding_balance` in `rules()` or a custom rule
    - _Requirements: 9.3, 16.3_

  - [x] 12.3 Create `StoreRejectionRequest`
    - Run `php artisan make:request Http/Requests/Api/V1/StoreRejectionRequest`
    - Rules: `rejection_reason` (required, string), `notes` (nullable, string)
    - _Requirements: 9.3, 17.4_

  - [x] 12.4 Create `StoreApprovalRequest` and `StoreDisbursementRequest`
    - Run `php artisan make:request Http/Requests/Api/V1/StoreApprovalRequest` — rules: `notes` (nullable, string)
    - Run `php artisan make:request Http/Requests/Api/V1/StoreDisbursementRequest` — rules: `notes` (nullable, string)
    - _Requirements: 9.3, 17.3_

- [x] 13. API Resources
  - [x] 13.1 Create `LoanResource`
    - Run `php artisan make:resource Http/Resources/Api/V1/LoanResource`
    - Expose: `id`, `user_id`, `principal_amount`, `outstanding_balance`, `interest_rate`, `repayment_term_months`, `interest_method`, `status`, `disbursed_at`, `eligibility_passed`, `notes`, `rejection_reason`, `created_at`
    - _Requirements: 8.2, 9.2_

  - [x] 13.2 Create `LoanScheduleResource`
    - Run `php artisan make:resource Http/Resources/Api/V1/LoanScheduleResource`
    - Expose: `id`, `loan_id`, `instalment_number`, `due_date`, `instalment_amount`, `principal_component`, `interest_component`, `outstanding_balance`, `status`, `remaining_amount`, `paid_at`
    - _Requirements: 6.2, 9.2_

  - [x] 13.3 Create `LoanRepaymentResource`
    - Run `php artisan make:resource Http/Resources/Api/V1/LoanRepaymentResource`
    - Expose: `id`, `loan_id`, `amount`, `transaction_id`, `created_at`
    - _Requirements: 9.2_

- [x] 14. Controllers under `App\Http\Controllers\Api\V1\`
  - [x] 14.1 Create `LoanController`
    - Run `php artisan make:controller Http/Controllers/Api/V1/LoanController`
    - `index`: authorize `viewAny`, return `LoanResource::collection(auth()->user()->loans()->latest()->paginate(15))`
    - `store`: authorize `create`, use `StoreLoanRequest`, create loan with `eligibility_checked_at` and `eligibility_passed` stamped, return `LoanResource` with 201
    - `show`: authorize `view` (or rely on scoped binding), return `LoanResource`
    - _Requirements: 1.1, 1.2, 8.1, 8.2, 8.3, 8.4, 9.1, 9.4_

  - [x] 14.2 Create `LoanEligibilityController`
    - Run `php artisan make:controller Http/Controllers/Api/V1/LoanEligibilityController`
    - `store`: call `CheckLoanEligibilityAction` for the authenticated user (no loan persisted); return JSON result with pass/fail and reason
    - _Requirements: 13.3, 13.4_

  - [x] 14.3 Create `LoanApprovalController`
    - Run `php artisan make:controller Http/Controllers/Api/V1/LoanApprovalController`
    - `store`: authorize `approve`, use `StoreApprovalRequest`, call `ApproveLoanAction`, return `LoanResource`
    - _Requirements: 3.1, 3.2, 9.1, 9.4_

  - [x] 14.4 Create `LoanDisbursementController`
    - Run `php artisan make:controller Http/Controllers/Api/V1/LoanDisbursementController`
    - `store`: authorize `disburse`, use `StoreDisbursementRequest`, call `DisburseLoanAction`, return `LoanResource`
    - _Requirements: 4.1, 4.4, 9.1, 9.4_

  - [x] 14.5 Create `LoanRejectionController`
    - Run `php artisan make:controller Http/Controllers/Api/V1/LoanRejectionController`
    - `store`: authorize `reject`, use `StoreRejectionRequest`, call `RejectLoanAction`, return `LoanResource`
    - _Requirements: 5.1, 5.3, 9.1, 9.4_

  - [x] 14.6 Create `LoanScheduleController`
    - Run `php artisan make:controller Http/Controllers/Api/V1/LoanScheduleController`
    - `index`: authorize `view` on the loan; throw 422 if loan status ≠ `Disbursed`; return `LoanScheduleResource::collection($loan->scheduleEntries()->orderBy('instalment_number')->get())`
    - _Requirements: 6.3, 6.4, 9.1, 9.4_

  - [x] 14.7 Create `LoanRepaymentController`
    - Run `php artisan make:controller Http/Controllers/Api/V1/LoanRepaymentController`
    - `store`: authorize `create` on the loan (or a dedicated gate); use `StoreRepaymentRequest`, call `RepayLoanAction`, return `LoanRepaymentResource` with 201
    - _Requirements: 7.1, 7.5, 7.6, 9.1, 9.4_

- [x] 15. Routes
  - [x] 15.1 Create `routes/api.php` and register all loan routes
    - Create the file; register `auth:sanctum` middleware group with `prefix('v1')`
    - Add rate-limited `POST loans` using the `loan-applications` limiter
    - Add all remaining routes: `GET loans`, `GET loans/{loan}`, `POST loans/eligibility`, `GET loans/{loan}/schedule`, `POST loans/{loan}/repayments`, `POST loans/{loan}/approve`, `POST loans/{loan}/disburse`, `POST loans/{loan}/reject`
    - Register `routes/api.php` in `bootstrap/app.php` via `->withRouting(api: __DIR__.'/../routes/api.php', ...)`
    - _Requirements: 9.1, 12.1_

- [x] 16. Checkpoint — wire everything together
  - Run `php artisan migrate` and `php artisan route:list --path=api/v1` to confirm all routes are registered and migrations run cleanly. Ask the user if anything looks off before proceeding to tests.

- [x] 17. Unit tests for eligibility and schedule calculation
  - [x] 17.1 Create `tests/Unit/Loans/LoanEligibilityCheckerTest.php`
    - Run `php artisan make:test --pest --unit Unit/Loans/LoanEligibilityCheckerTest`
    - Test `LoanEligibilityChecker` with mock specifications using `Mockery`
    - _Requirements: 2.2, 2.3, 2.4_

  - [ ]* 17.2 Write property test for eligib                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   ility checker (Property 3)
    - Dataset of 20+ randomised spec pass/fail combinations using `fake()`
    - Assert checker result matches AND-logic of all specs
    - **Property 3: Eligibility checker result matches specification outcomes**
    - **Validates: Requirements 2.2, 2.3, 2.4**

  - [ ]* 17.3 Write property test for `UserDurationSpecification` (Property 4)
    - Dataset of 20+ randomised account ages and thresholds
    - Assert `isSatisfiedBy()` returns true iff age ≥ threshold
    - **Property 4: UserDurationSpecification passes iff account age meets threshold**
    - **Validates: Requirements 2.5**

  - [ ]* 17.4 Write property test for `LoanLevelSpecification` (Property 5)
    - Dataset of 20+ randomised levels and amounts
    - Assert `isSatisfiedBy()` returns true iff amount ≤ level limit
    - **Property 5: LoanLevelSpecification passes iff amount is within level limit**
    - **Validates: Requirements 2.6**

  - [x] 17.5 Create `tests/Unit/Loans/GenerateLoanScheduleActionTest.php`
    - Run `php artisan make:test --pest --unit Unit/Loans/GenerateLoanScheduleActionTest`
    - Test flat-rate: entry count = term, sum of instalments = P + total_interest, balance progression
    - Test reducing-balance: entry count = term, final outstanding_balance ≈ 0
    - _Requirements: 6.1, 6.2, 6.5, 14.2, 14.3_

  - [ ]* 17.6 Write property test for schedule generation (Property 9)
    - Dataset of 20+ randomised principal/rate/term combinations
    - Assert entry count = term, sum of instalments within rounding tolerance, balance progression correct
    - **Property 9: Disbursement generates a complete loan schedule**
    - **Validates: Requirements 4.5, 6.1, 6.2, 6.5**

- [ ] 18. Feature tests — loan application
  - [-] 18.1 Create `tests/Feature/Loans/LoanApplicationTest.php`
    - Run `php artisan make:test --pest Feature/Loans/LoanApplicationTest`
    - Use `LazilyRefreshDatabase`; test successful application returns 201 with `active` status and correct `user_id`; test missing/invalid inputs return 422; test ineligible user returns 422 without persisting loan
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 13.2_

  - [ ]* 18.2 Write property test for loan application (Property 1)
    - Dataset of 20+ valid principal/term combinations
    - Assert each results in a persisted loan with `status = active` and correct `user_id`
    - **Property 1: Loan application persists with active status**
    - **Validates: Requirements 1.1, 1.5**

  - [ ]* 18.3 Write property test for invalid inputs (Property 2)
    - Dataset of 20+ invalid input combinations (zero, negative, non-numeric, missing)
    - Assert each returns HTTP 422
    - **Property 2: Invalid loan application inputs return 422**
    - **Validates: Requirements 1.3, 1.4**

- [ ] 19. Feature tests — loan approval
  - [ ] 19.1 Create `tests/Feature/Loans/LoanApprovalTest.php`
    - Run `php artisan make:test --pest Feature/Loans/LoanApprovalTest`
    - Test `active` loan transitions to `approved`; test non-`active` loan returns 422; test `LoanApproved` event dispatched; test `LoanApprovedNotification` sent; test `LoanStatusHistory` record created
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 17.1, 17.2_

  - [ ]* 19.2 Write property test for approval status transitions (Property 6 — approve)
    - Dataset of 20+ loans in various statuses
    - Assert only `active` loans transition; others return 422
    - **Property 6: Loan status transitions are enforced (approve)**
    - **Validates: Requirements 3.1, 3.2**

  - [ ]* 19.3 Write property test for event dispatch on approval (Property 7 — LoanApproved)
    - Dataset of 20+ valid `active` loans
    - Assert `LoanApproved` dispatched exactly once per approval
    - **Property 7: Lifecycle transitions dispatch the correct events (LoanApproved)**
    - **Validates: Requirements 3.3, 10.1**

- [ ] 20. Feature tests — loan disbursement
  - [ ] 20.1 Create `tests/Feature/Loans/LoanDisbursementTest.php`
    - Run `php artisan make:test --pest Feature/Loans/LoanDisbursementTest`
    - Test `approved` loan transitions to `disbursed`; test wallet credited with principal; test schedule generated; test non-`approved` loan returns 422; test `LoanDisbursed` event dispatched; test `LoanDisbursedNotification` sent
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [ ]* 20.2 Write property test for wallet credit on disbursement (Property 8)
    - Dataset of 20+ approved loans with varied principal amounts
    - Assert wallet balance increases by exactly the principal amount
    - **Property 8: Disbursement credits the user's General wallet**
    - **Validates: Requirements 4.2**

  - [ ]* 20.3 Write property test for disbursement status transitions (Property 6 — disburse)
    - Dataset of 20+ loans in various statuses
    - Assert only `approved` loans disburse; others return 422
    - **Property 6: Loan status transitions are enforced (disburse)**
    - **Validates: Requirements 4.1, 4.4**

- [ ] 21. Feature tests — loan rejection
  - [ ] 21.1 Create `tests/Feature/Loans/LoanRejectionTest.php`
    - Run `php artisan make:test --pest Feature/Loans/LoanRejectionTest`
    - Test `active`/`approved` loans transition to `rejected`; test `disbursed`/`rejected` loans return 422; test `rejection_reason` required; test `LoanRejected` event dispatched; test `LoanRejectedNotification` sent
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 17.4_

  - [ ]* 21.2 Write property test for rejection status transitions (Property 6 — reject)
    - Dataset of 20+ loans in all four statuses
    - Assert `active`/`approved` reject successfully; `disbursed`/`rejected` return 422
    - **Property 6: Loan status transitions are enforced (reject)**
    - **Validates: Requirements 5.1, 5.3**

- [ ] 22. Feature tests — loan schedule
  - [ ] 22.1 Create `tests/Feature/Loans/LoanScheduleTest.php`
    - Run `php artisan make:test --pest Feature/Loans/LoanScheduleTest`
    - Test schedule endpoint returns all entries for a disbursed loan; test non-disbursed loan returns 422; test entry fields are present
    - _Requirements: 6.3, 6.4_

  - [ ]* 22.2 Write property test for schedule endpoint status guard (Property 10)
    - Dataset of 20+ loans in `active`, `approved`, `rejected` statuses
    - Assert each returns HTTP 422 from the schedule endpoint
    - **Property 10: Schedule endpoint enforces disbursed status**
    - **Validates: Requirements 6.4**

- [ ] 23. Feature tests — loan repayment
  - [ ] 23.1 Create `tests/Feature/Loans/LoanRepaymentTest.php`
    - Run `php artisan make:test --pest Feature/Loans/LoanRepaymentTest`
    - Test repayment debits wallet, creates `LoanRepayment`, decrements `outstanding_balance`; test partial repayment updates `remaining_amount` without marking entry paid; test full instalment marks entry `Paid`; test overpayment returns 422; test non-disbursed loan returns 422; test `LoanSettled` event on full repayment
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 16.1, 16.2, 16.3_

  - [ ]* 23.2 Write property test for repayment wallet/balance/record (Property 11)
    - Dataset of 20+ disbursed loans with varied repayment amounts
    - Assert wallet decreases by A, `LoanRepayment` created, `outstanding_balance` decreases by A
    - **Property 11: Repayment debits wallet, records repayment, and updates balance**
    - **Validates: Requirements 7.1, 7.2, 7.3**

  - [ ]* 23.3 Write property test for schedule entry paid marking (Property 12)
    - Dataset of 20+ disbursed loans where repayment covers the next instalment
    - Assert `paid_at` is non-null on the satisfied entry
    - **Property 12: Repayment marks the next due schedule entry as paid**
    - **Validates: Requirements 7.4**

  - [ ]* 23.4 Write property test for repayment on non-disbursed loan (Property 13)
    - Dataset of 20+ loans in `active`, `approved`, `rejected` statuses
    - Assert each returns HTTP 422 on repayment attempt
    - **Property 13: Repayment on non-disbursed loan returns 422**
    - **Validates: Requirements 7.6**

- [ ] 24. Feature tests — loan listing
  - [ ] 24.1 Create `tests/Feature/Loans/LoanListingTest.php`
    - Run `php artisan make:test --pest Feature/Loans/LoanListingTest`
    - Test user only sees own loans; test cross-user loan access returns 404; test list ordered by `created_at` descending; test pagination at 15 per page
    - _Requirements: 8.1, 8.2, 8.3, 8.4_

  - [ ]* 24.2 Write property test for loan isolation (Property 14)
    - Dataset of 20+ user pairs with loans
    - Assert user A never sees user B's loans in list or show
    - **Property 14: Users only see their own loans**
    - **Validates: Requirements 8.1, 8.3**

  - [ ]* 24.3 Write property test for list ordering (Property 15)
    - Dataset of 20+ users each with multiple loans created at varied timestamps
    - Assert list is always ordered by `created_at` descending
    - **Property 15: Loan list is ordered by created_at descending**
    - **Validates: Requirements 8.4**

- [ ] 25. Architecture tests
  - [ ] 25.1 Add architecture tests to `tests/Arch/LoansTest.php`
    - Run `php artisan make:test --pest Arch/LoansTest`
    - Assert `App\Actions\Loans` classes have `Action` suffix
    - Assert `App\Loans\Specifications` classes implement `LoanSpecification`
    - Assert `App\Notifications\Loans` classes implement `ShouldQueue`
    - _Requirements: 2.1, 10.6, 18.5_

- [ ] 26. Final checkpoint — run full test suite
  - Run `php artisan test --compact` and ensure all tests pass. Run `vendor/bin/pint --dirty` to fix any style issues. Ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP
- Each task references specific requirements for traceability
- Checkpoints at tasks 16 and 26 ensure incremental validation
- Property tests use Pest datasets with 20+ Faker-generated cases per property (no external PBT library required)
- Unit tests validate specific examples and edge cases; property tests validate universal invariants
