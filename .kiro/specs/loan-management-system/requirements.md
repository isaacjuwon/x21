# Requirements Document

## Introduction

A backend loan management system for a Laravel 13 application. The system allows users to apply for loans, have those loans evaluated against configurable eligibility specifications, progress through a defined status lifecycle, generate repayment schedules, and make repayments via the existing wallet infrastructure. The design is extensible so new eligibility specification types can be added without modifying core loan logic.

## Glossary

- **Loan**: A financial product issued to a User with a principal amount, interest rate, and repayment schedule.
- **LoanApplication**: The initial request submitted by a User to borrow funds.
- **LoanStatus**: The lifecycle state of a loan — `active` (created/pending review), `approved`, `disbursed`, `rejected`.
- **LoanSpecification**: An interface-backed rule that determines whether a User is eligible for a loan. Implementations include `UserDurationSpecification` and `LoanLevelSpecification`.
- **UserDurationSpecification**: An eligibility rule based on how long the User account has existed.
- **LoanLevelSpecification**: An eligibility rule based on the User's current loan tier or level.
- **LoanSchedule**: A generated table of repayment instalments for a disbursed loan, each with a due date and amount.
- **LoanRepayment**: A single payment made by a User against a disbursed loan, recorded against the loan schedule.
- **Wallet**: The financial account managed by the `HasWallets` concern, used for all monetary movements.
- **WalletType**: An enum defining wallet categories (e.g., `General`) as provided by the existing `HasWallets` concern.
- **LoanEligibilityChecker**: The service that runs all active `LoanSpecification` instances against a User and returns a pass/fail result.
- **API**: The versioned JSON REST API exposed under `/api/v1/`.
- **InterestMethod**: An enum defining the interest calculation method for a loan — `FlatRate` (add-on interest applied to original principal) or `ReducingBalance` (amortisation, interest applied to outstanding balance each period).
- **LoanScheduleEntryStatus**: An enum defining the state of a single schedule instalment — `Pending` (not yet due or unpaid), `Paid` (fully settled), `Overdue` (past `due_date` and not fully paid).
- **LoanStatusHistory**: A record of every status transition on a loan, capturing `from_status`, `to_status`, timestamp, and the acting `user_id`.
- **LoanPolicy**: A Laravel Policy class that gates all loan actions — `viewAny`, `create`, `approve`, `disburse`, `reject` — ensuring only authorised users may perform each operation.

---

## Requirements

### Requirement 1: Loan Application Submission

**User Story:** As a user, I want to submit a loan application, so that I can request funds from the system.

#### Acceptance Criteria

1. WHEN a User submits a loan application with a valid principal amount and repayment term, THE LoanApplication SHALL be persisted with a status of `active`.
2. WHEN a User submits a loan application, THE API SHALL return the created loan resource with HTTP 201.
3. IF a User submits a loan application with a missing or invalid principal amount, THEN THE API SHALL return a validation error with HTTP 422.
4. IF a User submits a loan application with a missing or invalid repayment term, THEN THE API SHALL return a validation error with HTTP 422.
5. THE LoanApplication SHALL record the authenticated User as the applicant.

---

### Requirement 2: Loan Eligibility Checking via Specifications

**User Story:** As a system administrator, I want loan eligibility to be evaluated against configurable specifications, so that eligibility rules can be extended without modifying core loan logic.

#### Acceptance Criteria

1. THE LoanEligibilityChecker SHALL implement a `LoanSpecification` interface with a `isSatisfiedBy(User $user): bool` method.
2. WHEN eligibility is checked, THE LoanEligibilityChecker SHALL evaluate all registered `LoanSpecification` instances against the User.
3. WHEN all registered specifications are satisfied, THE LoanEligibilityChecker SHALL return `true`.
4. WHEN any registered specification is not satisfied, THE LoanEligibilityChecker SHALL return `false` and identify the failing specification.
5. THE UserDurationSpecification SHALL evaluate whether the User account age meets a configured minimum duration in days.
6. THE LoanLevelSpecification SHALL evaluate whether the User's current loan level permits a new loan of the requested amount.
7. WHERE a new `LoanSpecification` implementation is registered, THE LoanEligibilityChecker SHALL include it in all subsequent eligibility checks without modification to existing code.

---

### Requirement 3: Loan Approval

**User Story:** As an administrator, I want to approve a loan application, so that eligible users can receive funds.

#### Acceptance Criteria

1. WHEN an administrator approves a loan with status `active`, THE Loan SHALL transition to status `approved`.
2. IF an administrator attempts to approve a loan that is not in `active` status, THEN THE API SHALL return an error with HTTP 422.
3. WHEN a loan is approved, THE System SHALL dispatch a `LoanApproved` event.
4. WHEN a loan is approved, THE System SHALL notify the applicant User.

---

### Requirement 4: Loan Disbursement

**User Story:** As an administrator, I want to disburse an approved loan, so that funds are transferred to the user's wallet.

#### Acceptance Criteria

1. WHEN an administrator disburses a loan with status `approved`, THE Loan SHALL transition to status `disbursed`.
2. WHEN a loan is disbursed, THE System SHALL call `deposit()` on the applicant User via the `HasWallets` concern, crediting the principal amount to the `General` wallet.
3. WHEN a loan is disbursed, THE System SHALL dispatch a `LoanDisbursed` event.
4. IF an administrator attempts to disburse a loan that is not in `approved` status, THEN THE API SHALL return an error with HTTP 422.
5. WHEN a loan is disbursed, THE LoanSchedule SHALL be automatically generated for the loan.

---

### Requirement 5: Loan Rejection

**User Story:** As an administrator, I want to reject a loan application, so that ineligible or undesirable applications are declined.

#### Acceptance Criteria

1. WHEN an administrator rejects a loan with status `active` or `approved`, THE Loan SHALL transition to status `rejected`.
2. WHEN a loan is rejected, THE System SHALL dispatch a `LoanRejected` event.
3. IF an administrator attempts to reject a loan that is already `disbursed` or `rejected`, THEN THE API SHALL return an error with HTTP 422.
4. WHEN a loan is rejected, THE System SHALL notify the applicant User.

---

### Requirement 6: Loan Schedule Generation

**User Story:** As a user, I want to view my loan repayment schedule, so that I know when and how much to pay each instalment.

#### Acceptance Criteria

1. WHEN a loan is disbursed, THE System SHALL generate a `LoanSchedule` consisting of equal monthly instalments covering the full principal plus interest.
2. THE LoanSchedule SHALL contain one `LoanScheduleEntry` per repayment period, each with a due date, instalment amount, principal component, interest component, and outstanding balance.
3. WHEN a User requests the schedule for a disbursed loan, THE API SHALL return the full list of `LoanScheduleEntry` records.
4. IF a User requests the schedule for a loan that is not `disbursed`, THEN THE API SHALL return an error with HTTP 422.
5. THE LoanSchedule SHALL use a flat interest rate applied to the original principal for instalment calculation.

---

### Requirement 7: Loan Repayment

**User Story:** As a user, I want to make a repayment against my loan, so that I can reduce my outstanding balance.

#### Acceptance Criteria

1. WHEN a User submits a repayment for a `disbursed` loan, THE System SHALL call `withdraw()` on the User via the `HasWallets` concern, debiting the repayment amount from the `General` wallet.
2. WHEN a repayment is processed, THE System SHALL create a `LoanRepayment` record linked to the loan.
3. WHEN a repayment is processed, THE System SHALL update the outstanding balance on the loan.
4. WHEN a repayment amount satisfies the next due `LoanScheduleEntry`, THE System SHALL mark that entry as paid.
5. IF a User's wallet has insufficient funds for the repayment, THEN THE API SHALL return an error with HTTP 422.
6. IF a User attempts to repay a loan that is not in `disbursed` status, THEN THE API SHALL return an error with HTTP 422.
7. WHEN a repayment fully settles the loan outstanding balance, THE System SHALL dispatch a `LoanSettled` event.

---

### Requirement 8: Loan Listing and Retrieval

**User Story:** As a user, I want to list and view my loans, so that I can track my borrowing history and current status.

#### Acceptance Criteria

1. WHEN a User requests their loan list, THE API SHALL return only loans belonging to the authenticated User.
2. WHEN a User requests a specific loan, THE API SHALL return the loan resource including status, principal, outstanding balance, and repayment term.
3. IF a User requests a loan that does not belong to them, THEN THE API SHALL return HTTP 404.
4. THE API SHALL return loan lists ordered by `created_at` descending, paginated at 15 records per page by default.

---

### Requirement 9: API Versioning and Resources

**User Story:** As a developer, I want the loan API to be versioned and use Eloquent API Resources, so that the contract is stable and responses are consistent.

#### Acceptance Criteria

1. THE API SHALL expose all loan endpoints under the `/api/v1/` prefix.
2. THE API SHALL use Eloquent API Resources (`LoanResource`, `LoanScheduleResource`, `LoanRepaymentResource`) to format all responses.
3. THE API SHALL use Form Request classes for all input validation.
4. THE API SHALL return JSON responses with appropriate HTTP status codes for all success and error cases.

---

### Requirement 10: Eventing and Jobs

**User Story:** As a developer, I want loan lifecycle changes to emit events and use queued jobs for side effects, so that the system is decoupled and scalable.

#### Acceptance Criteria

1. THE System SHALL dispatch a `LoanApproved` event when a loan transitions to `approved`.
2. THE System SHALL dispatch a `LoanDisbursed` event when a loan transitions to `disbursed`.
3. THE System SHALL dispatch a `LoanRejected` event when a loan transitions to `rejected`.
4. THE System SHALL dispatch a `LoanSettled` event when a loan is fully repaid.
5. WHEN a `LoanDisbursed` event is fired, THE System SHALL dispatch a `GenerateLoanScheduleJob` to generate the repayment schedule asynchronously if not already generated synchronously.
6. THE System SHALL use queued jobs for any notification sending triggered by loan lifecycle events.

---

### Requirement 11: Authorization via LoanPolicy

**User Story:** As a system administrator, I want every loan action to be gated by a policy, so that only authorised users can perform sensitive operations.

#### Acceptance Criteria

1. THE LoanPolicy SHALL define gates for `viewAny`, `create`, `approve`, `disburse`, and `reject` actions.
2. WHEN any loan action is invoked, THE System SHALL authorize the request against the corresponding `LoanPolicy` gate before executing the action.
3. IF a User is not authorized for a given action, THEN THE API SHALL return HTTP 403.
4. THE LoanPolicy SHALL be registered and applied consistently across all loan controllers.

---

### Requirement 12: Rate Limiting on Loan Application

**User Story:** As a system administrator, I want the loan application endpoint to be rate-limited, so that users cannot flood the system with applications.

#### Acceptance Criteria

1. THE API SHALL enforce a rate limit of 5 loan application requests per minute per authenticated User on the `POST /api/v1/loans` endpoint.
2. IF a User exceeds the rate limit, THEN THE API SHALL return HTTP 429.

---

### Requirement 13: Eligibility Check Persistence and Pre-Check Endpoint

**User Story:** As a developer, I want eligibility results stored on the loan and available via a dedicated endpoint, so that eligibility outcomes are auditable and users can check eligibility before applying.

#### Acceptance Criteria

1. WHEN a loan application is submitted, THE System SHALL run `CheckLoanEligibilityAction` and store the result on the loan record as `eligibility_checked_at` (timestamp) and `eligibility_passed` (boolean).
2. IF a User is ineligible at application time, THEN THE API SHALL return HTTP 422 with the failing `LoanSpecification` reason and SHALL NOT persist the loan.
3. THE API SHALL expose a `POST /api/v1/loans/eligibility` endpoint that runs the eligibility check for the authenticated User and returns the result without creating a loan record.
4. WHEN the eligibility pre-check endpoint is called, THE API SHALL return whether the User passes all specifications and, if not, the reason from the failing specification.

---

### Requirement 14: Interest Method Selection

**User Story:** As a system administrator, I want loans to support both flat-rate and reducing-balance interest methods, so that different loan products can be offered.

#### Acceptance Criteria

1. THE Loan SHALL store an `interest_method` field using the `InterestMethod` enum with values `FlatRate` and `ReducingBalance`.
2. WHEN a loan schedule is generated with `InterestMethod::FlatRate`, THE GenerateLoanScheduleAction SHALL apply add-on interest to the original principal for all instalments.
3. WHEN a loan schedule is generated with `InterestMethod::ReducingBalance`, THE GenerateLoanScheduleAction SHALL apply amortisation, calculating interest on the outstanding balance for each period.
4. THE `interest_method` SHALL be set at loan application time and SHALL NOT change after the loan is created.

---

### Requirement 15: Loan Schedule Entry Status

**User Story:** As a user, I want each schedule entry to have an explicit status, so that I can clearly see which instalments are pending, paid, or overdue.

#### Acceptance Criteria

1. THE LoanScheduleEntry SHALL store a `status` field using the `LoanScheduleEntryStatus` enum with values `Pending`, `Paid`, and `Overdue`.
2. WHEN a `LoanScheduleEntry` is created, THE System SHALL set its status to `Pending`.
3. WHEN a `LoanScheduleEntry` is fully paid, THE System SHALL set its status to `Paid`.
4. WHILE a `LoanScheduleEntry` has a `due_date` in the past and is not fully paid, THE System SHALL treat its status as `Overdue`.

---

### Requirement 16: Partial Repayments and Overpayment Handling

**User Story:** As a user, I want to make partial repayments and be protected from accidental overpayment, so that I have flexibility in how I repay my loan.

#### Acceptance Criteria

1. WHEN a User submits a repayment amount less than the next instalment amount, THE System SHALL reduce the `LoanScheduleEntry`'s remaining amount by the payment and SHALL NOT mark the entry as `Paid` until the full instalment is covered.
2. WHEN a User submits a repayment amount that fully covers the next instalment's remaining amount, THE System SHALL mark that `LoanScheduleEntry` as `Paid`.
3. IF a User submits a repayment amount that exceeds the loan's outstanding balance, THEN THE API SHALL return HTTP 422 and SHALL only debit the outstanding balance amount, rejecting the excess.

---

### Requirement 17: Loan Status History

**User Story:** As a system administrator, I want every loan status transition to be recorded, so that I have a full audit trail of loan lifecycle changes.

#### Acceptance Criteria

1. THE System SHALL maintain a `loan_status_histories` table recording every status transition with `from_status`, `to_status`, a timestamp, and the `user_id` of the actor who triggered the transition.
2. WHEN any loan status transition occurs (approve, disburse, reject, settle), THE System SHALL insert a `LoanStatusHistory` record.
3. THE Loan SHALL store an optional `notes` field for administrator context, which is optional on approve and disburse actions.
4. WHEN an administrator rejects a loan, THE System SHALL require a `rejection_reason` to be provided and store it on the loan record.

---

### Requirement 18: Named Notification Classes

**User Story:** As a developer, I want named, queueable notification classes for each loan lifecycle event, so that notifications are decoupled, testable, and scalable.

#### Acceptance Criteria

1. THE System SHALL implement a `LoanApprovedNotification` class that implements `ShouldQueue` and is dispatched when a loan is approved.
2. THE System SHALL implement a `LoanDisbursedNotification` class that implements `ShouldQueue` and is dispatched when a loan is disbursed.
3. THE System SHALL implement a `LoanRejectedNotification` class that implements `ShouldQueue` and is dispatched when a loan is rejected.
4. THE System SHALL implement a `LoanSettledNotification` class that implements `ShouldQueue` and is dispatched when a loan is fully repaid.
5. WHEN a loan lifecycle event is fired, THE System SHALL send the corresponding named notification to the applicant User via the queued notification class.
