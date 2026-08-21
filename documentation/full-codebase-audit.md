# Full Codebase Audit

## Audit scope

Repository: `nvnkmr127/privyr-1`

Audited ref: `e47d3de0f48897b5fb8205edf9f7d4a56263951f`

Audit branch: `audit/full-codebase-audit-2026-08-21`

Product scope used for this audit: Lead Operations only. The target system is responsible for lead capture, centralization, qualification, assignment, lifecycle, pipeline, follow-up, nurturing, activities, communication, duplicate control, automation, SLA, attribution, data quality, analytics, imports, APIs, webhooks, permissions, auditability, and lead data protection.

The supplied product specification explicitly requires implementation proof at execution-path level. A route, model, migration, service, menu item, or UI component is not treated as proof of completion. fileciteturn0file0

No application code was modified during this audit. The only repository changes are the three audit Markdown files created on this branch.

## Audit method and evidence standard

The repository was inspected through the connected GitHub source at the current main commit. The supplied documentation was used as reference material, then challenged against actual source code. The source documentation itself states that the application uses Laravel, package-based modules, repositories, events, queues, and Form Requests. Those architectural claims were checked against the implementation rather than accepted as proof. fileciteturn0file4L1-L2

The supplied documentation also states that Controllers should remain thin and delegate persistence to repositories. fileciteturn0file5L1-L2

Test execution could not be performed in this audit environment because outbound GitHub DNS was unavailable for a local checkout. No test result is claimed. The repository CI files were inspected to identify the exact intended commands.

## 1. Executive Summary

The codebase contains a substantial Lead Operations implementation layered onto Krayin CRM. The Lead module includes ingestion services, source connectors, duplicate matching, assignment rules, follow-up state, nurturing, qualification, scoring, attribution, audits, analytics, bulk operations, imports, webhooks, QR capture, and lead visibility controls.

The main problem is architectural consistency. Multiple Lead paths exist, and several bypass the canonical business services. The result is a system where the same user action has different behavior depending on entry point.

The highest-risk findings are:

1. `LeadController::updateStatus()` contains two consecutive identical `catch (\Exception $exception)` clauses. This is a PHP syntax error and is a production blocker.
2. Unified Inbox single-lead and legacy bulk actions modify leads directly without the Lead Policy or LeadVisibilityService. These paths accept client supplied Lead IDs, so they require an explicit object-level authorization check.
3. The Lead DataGrid contains SQL subqueries against `lead_slas`, while repository searches did not find a corresponding `lead_slas` migration or service implementation. This is a strong indication that the SLA grid path is incomplete or broken.
4. Public webhook endpoints rely on bearer-like connector tokens but lack a consistent signature, replay, idempotency, and rate-limiting layer. Facebook signature verification is optional when configuration is missing and missing signatures are accepted.
5. `lead.create.after` is dispatched by both repository creation and higher-level ingestion/controller flows, so workflow listeners risk firing twice.
6. Duplicate detection is implemented twice with materially different matching logic. Manual capture uses `LeadDuplicateService`, while canonical ingestion uses `DuplicateMatchingService`. This introduces inconsistent deduplication behavior and duplicate database work.
7. Lifecycle services are incomplete. `convertLead`, `markLost`, and `markJunk` reference `leadNurtureService` and `leadFollowUpService`, but these dependencies are not present in the constructor shown in the implementation.
8. Lead nurturing accepts an arbitrary outcome string, while lifecycle status values are intended to be constrained. This lets a caller bypass the lifecycle status contract.
9. Follow-up completion contains `dump()` statements, and cancellation marks activities as completed instead of using a distinct cancelled state.
10. Lead field auditing uses `getDirty()` inside the Eloquent `updated` event, which is a fragile implementation for post-update change detection and is inconsistent with the safer `getChanges()` usage already present in `LeadObserver`.
11. The Lead form still validates `products`, and the repository still contains Quote-related Lead controllers. This violates the new Lead-only product boundary and leaves old commercial entities inside the Lead creation path.
12. The original Lead migration still includes legacy Person and `lead_stage_id` schema, while the current model uses `person_name` and `lead_pipeline_stage_id`. The schema transition needs a dedicated cleanup and integrity review.

Overall readiness: **RED, not production-ready**.

The current branch requires P0 and P1 remediation before functional completeness should be claimed.

## 2. Current Architecture

The application is a Laravel 12 project with package-based modules under `packages/Webkul`. Composer registers packages for Activity, Admin, Attribute, Contact, DataTransfer, Email, Lead, Tag, User, WebForm, Automation and others. fileciteturn0file0

The repository includes a Lead-specific service layer and the LeadServiceProvider binds `LeadIngestionService`, registers the Lead policy, observer, audit subscriber, and the `lead:evaluate-health` command. File: `packages/Webkul/Lead/src/Providers/LeadServiceProvider.php`, lines 1-43.

Current Lead architecture is roughly:

```text
Manual UI
    |
    v
LeadController
    |
    +--> LeadForm
    +--> LeadDuplicateService
    +--> LeadRepository
    +--> Lifecycle / Nurture / Follow-up / Assignment services

External sources
    |
    v
PublicLeadCaptureController
    |
    v
LeadCaptureService
    |
    v
LeadIngestionService
    |
    +--> Data Quality
    +--> Duplicate Matching
    +--> Attribution
    +--> LeadRepository

Lead model
    |
    +--> LeadObserver
    +--> LeadAuditSubscriber
    +--> Activities
    +--> Qualifications
    +--> Assignments
    +--> Nurture
    +--> Attribution
    +--> Tags
```

The architecture has good building blocks, but business rules are split between Controller, Repository, Eloquent model, Observer, services, legacy bulk paths, and ingestion paths. This is the main architectural risk.

## 3. Current Lead Workflow

### Manual creation

Actual path:

```text
POST /admin/leads/create
    -> LeadForm
    -> LeadController::store
    -> LeadDuplicateService
    -> pipeline/stage resolution
    -> LeadRepository::create
    -> response
```

The controller dispatches `lead.create.before`, then calls the duplicate service and repository. For non-AJAX requests it also dispatches `lead.create.after`, while the repository creation path already dispatches the same event. This creates a duplicate event risk.

Evidence:

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`
Line: 367-420
Finding: Controller dispatches `lead.create.after` after repository creation except in the AJAX branch.
Expected: One authoritative create-after event in one canonical layer.
Impact: Workflow, notification, scoring, or analytics listeners may execute twice for non-AJAX creates and only once through AJAX.
Severity: P1

### External ingestion

Actual path:

```text
Public webhook / connector endpoint
    -> connector token lookup
    -> optional provider-specific signature check
    -> LeadCaptureService
    -> mapping
    -> LeadDuplicateService
    -> LeadIngestionService
    -> normalization + validation
    -> DuplicateMatchingService
    -> LeadAttributionService
    -> LeadRepository
    -> attribution history
    -> ingestion events
```

This is a strong architectural direction, but duplicate checking occurs twice using different services, and create-after is also dispatched twice.

## 4. Feature Completion Matrix

The detailed row-level matrix is in `documentation/audit-feature-matrix.md`.

Overall classification from the audited scope:

- Complete: core CRUD, visibility policy, assignment rules, attribution history, qualification history, many activity paths.
- Partial: import, duplicate merge, nurturing, follow-up, analytics, communication abstraction, automation, API authorization.
- Broken or incorrect: status endpoint, SLA integration, some bulk paths, parts of lifecycle orchestration.
- Missing: robust replay protection, field-level authorization, reliable Lead archive/restore lifecycle, comprehensive SLA engine, automation idempotency and loop prevention.
- Out of scope but still present: Person, Contact, Organization, Quote, Product dependencies.

## 5. Missing Features

### Lead archive lifecycle

The current code has `is_archived`, archive and unarchive actions, but Lead deletion is still hard deletion in the inherited repository flow. There is no reviewed restore-from-delete flow and no dependency-aware soft delete strategy.

The original Lead schema does not define a Laravel SoftDeletes column. File: `packages/Webkul/Lead/src/Database/Migrations/2021_04_22_164215_create_leads_table.php`, lines 1-42.

Severity: P1.

### Reliable SLA engine

The DataGrid expects four SLA states from `lead_slas`:

- Assignment
- First Action
- Follow-up
- Stage

No corresponding implementation was verified in the audited source tree. Treat the SLA surface as incomplete until the table, creation rules, scheduler, breach handling, escalation, history, and dashboard consumers are verified end-to-end.

Severity: P1.

### Webhook replay protection

Connector tokens provide source routing but do not establish single-use request semantics. There is no consistent event ID + signature + timestamp + replay window enforcement across the public endpoints.

Severity: P1.

### Automation loop prevention

The repository does not show a durable execution ledger with a workflow execution ID, source event ID, attempt number, or recursion guard. This is required for reliable lead automation.

Severity: P1.

### Field-level permission model

Lead policy controls record access but there is no comparable field-level authorization layer for sensitive attributes. A role either updates a Lead or does not. There is no evidence of enforcing per-field read/write restrictions.

Severity: P2.

## 6. Incomplete Features

### Qualification

Qualification itself is implemented and records history. The service also invokes scoring. However, bulk qualification has a fallback branch that directly changes `qualification_status` because `LeadLifecycleService::changeQualificationStatus` is absent. This creates a second qualification update path.

File: `packages/Webkul/Lead/src/Services/BulkLeadOperationService.php`
Lines: 207-215.
Severity: P1.

### Assignment

Manual and rule-based assignment are strong features. The rule engine supports direct, team, round-robin, weighted, least-assigned, and capacity-based assignment. However, rule condition expressiveness is limited to simple single-value operators, and the automatic path updates `leads` using `DB::table()` instead of model/repository persistence.

File: `packages/Webkul/Lead/src/Services/LeadAssignmentService.php`
Lines: 1-94, 145-230.
Severity: P1 for consistency, P2 for feature depth.

### Follow-up

Next-action synchronization exists and overdue state is computed, but follow-up history is not a dedicated first-class domain object. Completion also contains debug output.

File: `packages/Webkul/Lead/src/Services/LeadFollowUpService.php`
Lines: 120-183.
Severity: P1.

### Nurturing

Nurture history and re-engagement data exist, but start and completion manipulate lifecycle state through a separate service. Completion accepts arbitrary strings instead of validating against allowed lifecycle statuses.

File: `packages/Webkul/Lead/src/Services/LeadNurtureService.php`, lines: approximately 18-155.
Severity: P1.

### Analytics

Core, source, owner, pipeline and trend services exist. The facade explicitly returns `0` and empty score buckets for `aging_leads_avg_days`, `score_distribution`, and `avg_response_time_hours`.

File: `packages/Webkul/Lead/src/Services/LeadAnalyticsService.php`, lines 25-48.
Finding: Dashboard output is intentionally mocked/omitted.
Severity: P2.

## 7. Broken Features

### P0: Lead status endpoint syntax error

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`
Function: `updateStatus()`
Lines: 623-648

Finding:

```php
} catch (\Exception $exception) {
} catch (\Exception $exception) {
```

Two identical catch clauses are consecutive. This is invalid PHP syntax.

Expected: One catch block returning the error response.

Impact: PHP parsing fails before the controller can load. This is a production blocker.

Recommended fix: Remove the duplicate catch and add a regression test for the status endpoint.

### P0/P1: Unified Inbox object authorization gap

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`
Functions: `swipeAction()`, `bulkAction()`
Lines: 143-194 and 199-260.

Finding: The methods accept Lead IDs from request input and update records directly. They do not call `$this->authorize()` or LeadVisibilityService before mutation.

Expected: Every mutating Lead path must enforce the same object-level visibility policy as normal Lead update/delete endpoints.

Impact: A user who reaches these routes may attempt operations against another user’s Lead by changing the Lead ID.

Severity: P0 until proven otherwise by route ACL and policy tests.

Recommended fix: Route the operations through a single policy-aware bulk service and prohibit direct query-builder updates in controller paths.

### P1: SLA query references unverified table

File: `packages/Webkul/Admin/src/DataGrids/Lead/LeadDataGrid.php`
Lines: 145-163.

Finding: The grid builds SQL against `lead_slas` for four SLA statuses.

Expected: A corresponding migration, model, service, scheduler, and tests must exist.

Impact: Lead listing/export can fail at query time if the table is absent. If the table exists outside the inspected package, the implementation is still undocumented and unverified.

Severity: P1.

Recommended fix: verify schema and runtime registration, then complete the full SLA lifecycle or remove dead SLA UI references.

### P1: Missing provider signature enforcement

File: `packages/Webkul/Lead/src/Services/LeadCaptureService.php`
Function: `assertValidFacebookSignature()`
Lines: 225-242.

Finding: The method returns when the secret, signature, or raw body is absent.

Expected: If a provider endpoint is configured for signed delivery, a missing or invalid signature must reject the request.

Impact: An attacker who knows the connector endpoint token can inject leads without provider authenticity.

Severity: P1.

Recommended fix: Make signature verification mandatory for providers that support signatures and explicitly configure which providers use which verification method.

### P1: Generic public endpoint replay protection is missing

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/PublicLeadCaptureController.php`
Lines: 18-94 and related provider handlers.

Finding: IndiaMART, JustDial, real estate portal and generic connector handlers resolve connectors by token, then call ingestion. No timestamp, event ID, nonce, or replay-window check is visible.

Severity: P1.

Recommended fix: Require an authenticated signed envelope or provider-specific event ID and store processed events with unique constraints.

## 8. Backend Gaps

1. Multiple creation paths do not share one single application service boundary.
2. Multiple bulk mutation implementations exist: legacy Inbox bulk actions and `BulkLeadOperationService`.
3. Lifecycle logic is spread across `LeadController`, `LeadLifecycleService`, `LeadNurtureService`, `LeadFollowUpService`, and observers.
4. Assignment rule execution bypasses model events via `DB::table()`.
5. Attribution is strong but is triggered only through ingestion-oriented paths.
6. Direct `Lead::update()` calls remain in controllers and services for fields with business side effects.
7. `LeadIngestionService` contains duplicate matching responsibilities across services.

## 9. Frontend Gaps

The current repository contains Blade/Vue-driven Lead views and a large set of Lead routes. The requested UI audit requires runtime browser execution to verify every button, modal, error state, and mobile flow. The GitHub workflow shows Playwright tests are intended to run in six shards from `packages/Webkul/Admin/tests/e2e-pw`.

The current audit therefore classifies UI completion from code-path evidence, not as a claim of successful browser execution.

Specific code-level risks:

- UI routes exist for features whose backend path has broken or inconsistent authorization.
- Inbox actions expose direct mutation endpoints separate from the primary Lead edit flow.
- SLA status columns exist without verified backend storage.
- Bulk operations exist in more than one implementation.

## 10. Database Gaps

The original Lead migration contains legacy fields such as `person_id`, a boolean `status`, and `lead_stage_id`, while the current Lead model and controllers use `person_name`, string lifecycle statuses, and `lead_pipeline_stage_id`.

File: `packages/Webkul/Lead/src/Database/Migrations/2021_04_22_164215_create_leads_table.php`, lines 14-35.

The product scope now excludes Person/Contact/Organization functionality, so the continued presence of `person_id` and contact-oriented legacy schema is a migration and dependency cleanup concern.

The migration also defines `user_id` as non-null while current Lead functionality supports unassigned state. This should be reconciled in the final schema, otherwise database and business semantics disagree.

The August 2026 Inbox migration adds priority, unread state, contact timestamps, next follow-up, and archive state, but no indexes are declared for these high-traffic operational fields.

File: `packages/Webkul/Lead/src/Database/Migrations/2026_08_13_000001_add_inbox_attributes_to_leads_table.php`, lines 10-26.

## 11. API Gaps

The application has public ingestion endpoints and application API/controllers for Lead capture. The canonical ingestion service has transaction boundaries, validation, duplicate checks, external IDs, and error logging.

Remaining API gaps:

- consistent authentication model across all connectors
- signature verification across all signed providers
- request idempotency keys
- provider event uniqueness constraints
- rate limiting
- structured error contract with stable machine-readable codes
- explicit API permission mapping per operation
- secure duplicate response details

## 12. Integration Gaps

The main integration architecture is connector-driven and supports Meta, Google, IndiaMART, JustDial, real estate portals, Zapier, Make, generic webhooks, QR capture and API-style ingestion.

The integration layer still has inconsistent security semantics. Token-only endpoints and optional signature verification do not provide a uniform trust model.

## 13. Automation Gaps

The repository contains Workflow infrastructure and Lead events. The Lead module emits many domain events, including ingestion, attribution, assignment, lifecycle, qualification, nurturing, follow-up, and stage-related events.

The missing reliability layer is durable automation execution control:

- execution ID
- event ID
- deduplication key
- attempt count
- retry schedule
- max retries
- loop depth/recursion protection
- idempotent action keys
- execution audit record

Without these controls, event-driven Lead automation is not safe enough for production-scale operations.

## 14. Security Issues

### P0/P1

- Object-level authorization must be enforced on every Lead mutation path.
- Public webhook security is inconsistent.
- Replay protection is missing.
- Error responses from public capture endpoints return raw exception messages.

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/PublicLeadCaptureController.php`, lines 44-56.

The supplied security documentation says CSRF, fillable protection, parameter binding, and Blade escaping are core controls. fileciteturn0file2L1-L4 Those controls do not replace object-level authorization or signed webhook verification.

## 15. Performance Issues

### High

1. Lead DataGrid export builds correlated EAV subqueries for every custom attribute for every exported Lead. This scales poorly with large Lead volumes.
2. Lead model accessors such as `next_follow_up`, `last_contacted`, and follow-up counters query activities independently. Serializing many Leads risks N+1 database queries.
3. Lead scoring loads every active rule per evaluation and reevaluates all rules on each qualifying event.
4. Duplicate matching queries JSON columns and may not benefit from conventional indexes.

### Medium

5. Lead assignment `least_assigned` and `capacity_based` recalculate counts from `leads` per assignment evaluation.
6. Bulk actions process up to 100 Leads synchronously in one transaction chunk, which is risky for large operations.

## 16. Permission Issues

LeadPolicy is correctly registered and provides view/update/delete decisions through LeadVisibilityService. fileciteturn0file1L1-L4

However, policy enforcement is inconsistent across controllers. The presence of a correct policy does not secure a route that never calls it.

The most important example is `swipeAction()` and legacy `bulkAction()`.

## 17. Data Integrity Issues

1. Duplicate `lead.create.after` events.
2. Two duplicate matching services with different criteria.
3. Status values stored as a modern string contract while the original migration defines a boolean status.
4. User assignment supports null in application logic while the original `users` foreign key is non-null.
5. Nurture completion accepts arbitrary status strings.
6. Direct database assignment bypasses normal Eloquent observers.
7. Follow-up cancellation uses completed state instead of a distinct cancelled state.

## 18. Dead / Unused / Out-of-Scope Code

The product boundary explicitly removes Contact Management, Person Management, Organization Management, Quote Management, Deal Management, Sales Orders, ERP, and broad Marketing Automation. fileciteturn0file0

The repository still contains:

- `Webkul/Contact`
- Person/Organization automation entity helpers
- Quote controllers
- Product validation inside LeadForm
- inherited marketing modules

These items should be treated as dependency candidates for removal only after a dependency map is completed. They were not deleted during this audit.

## 19. TODO / FIXME Findings

The audit found code comments documenting incomplete behavior inside live Lead services.

Examples:

- Lead Analytics explicitly mocks metrics with zero values.
- Lead Health states that a robust event de-duplication table is not implemented.
- Bulk filtering contains comments describing incomplete filter logic.
- Lead merge notes say ACL must be handled by the controller.

Each is classified as technical debt or incomplete implementation rather than harmless documentation because the missing behavior changes runtime correctness.

## 20. Person / Contact / Organization Dependencies

Dependency map:

```text
Legacy Lead schema
  -> person_id

Lead forms / inherited code
  -> Person / Contact package references

Automation entity helpers
  -> Person
  -> Quote
  -> Lead

Product validation
  -> products table

Current Lead model
  -> person_name, emails, contact_numbers
  -> no Person relation in the inspected model
```

This is a favorable direction because the current Lead model is already moving toward Lead-owned identity fields, but the old packages and schema remain.

Safe removal order:

1. inventory all runtime references
2. replace business logic references with Lead-owned fields
3. remove routes and menus
4. remove permissions
5. migrate data
6. drop obsolete tables only after data verification
7. remove Composer package autoloads and tests

## 21. Testing Status

No local test execution result is claimed because the audit environment could not resolve GitHub for a local clone.

The repository CI specifies:

```text
composer install --no-interaction --prefer-dist --no-progress
php artisan krayin-crm:install --skip-env-check --skip-admin-creation
vendor/bin/pest --parallel --colors=always
```

File: `.github/workflows/ci.yml`, lines 1-52.

The Playwright workflow specifies:

```text
npm install
npx playwright install --with-deps
composer install
php artisan krayin-crm:install --skip-env-check --skip-admin-creation
php artisan optimize:clear
npx playwright test --reporter=list --config=tests/e2e-pw/playwright.config.ts --shard=N/6
```

File: `.github/workflows/admin_playwright_tests.yml`, lines 1-100.

No workflow runs or commit status were available through the connected GitHub status endpoints for the audited commit, so there is no verified pass/fail result to report.

## 22. Build / Runtime Errors

Verified code-level error:

- Duplicate catch clause in `LeadController::updateStatus()`.

Potential runtime error:

- `lead_slas` SQL dependency in LeadDataGrid without verified table/service.

Potential runtime errors:

- Lifecycle service methods reference injected services that are not shown in the constructor.

These should be confirmed with the project's own PHP and Pest commands after the repository is checked out in a runtime environment.

## 23. Technical Debt

The main technical debt category is duplicate business paths.

Examples:

- two duplicate detection services
- two bulk mutation implementations
- direct model updates and repository updates for similar fields
- observer logging plus audit subscriber logging
- lifecycle logic split across several services
- legacy Contact/Quote modules inside a Lead-only product

This debt is more important than cosmetic refactoring because it creates behavioral divergence.

## 24. P0 Issues

1. LeadController status endpoint syntax error.
2. Potential Lead IDOR through Inbox mutation endpoints until policy coverage is verified.
3. Any verified production use of a missing `lead_slas` table that causes Lead list queries to fail.

P0 means release-blocking security, integrity, or runtime failure.

## 25. P1 Issues

1. Webhook signature and replay security.
2. Duplicate create-after event dispatch.
3. Two duplicate matching engines.
4. Lifecycle service dependency inconsistency.
5. Nurture status validation gap.
6. Legacy bulk path bypassing services and history.
7. Automatic assignment bypassing Eloquent events.
8. Missing archive/restore domain model.
9. Missing robust automation idempotency.
10. Incomplete SLA engine.
11. Follow-up cancellation semantics.

## 26. P2 Issues

1. Analytics metrics mocked as zeros.
2. EAV export scalability.
3. Lead scoring scalability.
4. Advanced assignment condition expressiveness.
5. Field-level permission model.
6. Dedicated follow-up history.
7. Broader reporting completeness.

## 27. P3 Issues

1. Duplicate import statement in `LeadIngestionService`.
2. Documentation drift from the new Lead-only product boundary.
3. Legacy modules retained until dependency cleanup.
4. Minor naming and consistency issues.

## 28. Recommended Fix Order

```text
P0 Runtime
  1. Fix LeadController updateStatus syntax error.
  2. Secure Inbox swipe and bulk mutation paths with Lead Policy / visibility.
  3. Confirm or remove the lead_slas DataGrid dependency.

P0/P1 Security
  4. Build one webhook verification policy.
  5. Add replay protection and event idempotency.
  6. Add rate limits to public capture endpoints.
  7. Return stable error codes, not raw exception messages.

P1 Data integrity
  8. Consolidate duplicate detection.
  9. Consolidate create event dispatch.
  10. Consolidate lifecycle mutations.
  11. Make stage and assignment mutations use one service boundary.
  12. Introduce database uniqueness / locking for external IDs.

P1 Operations
  13. Complete follow-up state machine and cancellation semantics.
  14. Complete SLA engine.
  15. Make automation execution idempotent and loop-safe.

P1/P2 Scope cleanup
  16. Remove Quote/Product dependencies from Lead flows.
  17. Complete Person/Contact/Organization dependency map and migration plan.

P2 Analytics / performance
  18. Replace mocked metrics.
  19. Optimize Lead DataGrid exports and N+1 accessors.
  20. Add comprehensive end-to-end tests for every Lead entry point.
```

## 29. Dependency Map

```text
Lead UI
  -> LeadController
      -> LeadForm
      -> LeadPolicy
      -> LeadDuplicateService
      -> LeadRepository
      -> Lifecycle/Nurture/Follow-up/Assignment

LeadDataGrid
  -> Lead model
  -> VisibilityService
  -> EAV
  -> SLA query

Public connectors
  -> PublicLeadCaptureController
  -> LeadCaptureService
  -> LeadIngestionService
      -> DataQuality
      -> DuplicateMatchingService
      -> Attribution
      -> LeadRepository

Lead model
  -> Observer
  -> Audit subscriber
  -> Activities
  -> Qualification
  -> Assignment history
  -> Nurture
  -> Attribution

Out-of-scope dependencies
  -> Contact / Person / Organization
  -> Quote
  -> Products
  -> broad Marketing modules
```

## 30. Final Product Readiness Assessment

### Operational readiness

**RED**.

The product has strong Lead Operations foundations but does not yet have the consistency required for a reliable production system.

### Security readiness

**RED** until public ingestion security and Inbox object-level authorization are closed.

### Data integrity readiness

**RED** until duplicate detection, event dispatch, lifecycle, schema migration, and bulk mutation paths are consolidated.

### Feature completeness

**YELLOW/RED**. Many features exist, but several are partial, mocked, or connected through divergent paths.

### Architectural readiness

**YELLOW**. The service-oriented direction is good, but duplicated pathways make behavior difficult to trust.

## 31. Final Summary

The requested audit list contains 191 individual capability checks when each subfeature is counted separately. The feature matrix groups related checks into traceable clusters but keeps every requested capability covered.

Complete: 61

Partial: 68

Broken/Incorrect: 37

Missing: 19

UI only: 0

Backend only: 0

Unused/Out-of-scope: 6

Security issues: 9

Performance issues: 6

P0: 3

P1: 11

P2: 7

P3: 4

Then provide:

## TOP 20 THINGS TO FIX FIRST

Order them by:

1. Security
2. Data integrity
3. Core Lead workflow
4. Broken integrations
5. Permission issues
6. Performance
7. Missing functionality
8. UX
9. Cleanup

The actual ordered list is:

1. Fix the duplicate `catch` syntax error in `LeadController::updateStatus()`.
2. Add Policy/visibility checks to Inbox swipe mutations.
3. Add Policy/visibility checks to legacy Inbox bulk mutations.
4. Verify the existence and runtime provisioning of `lead_slas`.
5. Implement provider-specific webhook signature requirements.
6. Add replay prevention using external event IDs and timestamps.
7. Add rate limiting to all public Lead capture routes.
8. Return sanitized public webhook errors.
9. Consolidate `LeadDuplicateService` and `DuplicateMatchingService`.
10. Add database-level uniqueness for origin + external ID.
11. Make `lead.create.after` fire once from one authoritative layer.
12. Consolidate lifecycle, nurture, follow-up, and bulk status mutations.
13. Fix missing lifecycle service dependencies.
14. Validate nurture completion outcomes against allowed lifecycle statuses.
15. Replace `dump()` statements in follow-up completion.
16. Introduce an explicit cancelled follow-up state.
17. Complete the SLA engine and its scheduler.
18. Add automation execution idempotency and loop protection.
19. Remove Quote/Product validation from Lead workflows.
20. Complete Person/Contact/Organization dependency cleanup before removing legacy schema.

## Audit conclusion

The repository is not a failed product. It is a partially consolidated Lead Operations system with a good set of building blocks. The next phase should focus on consolidation, correctness, security, and execution-path reliability rather than adding more top-level features.

Do not add new Lead features until the P0 issues are closed and the canonical Lead ingestion, mutation, assignment, lifecycle, automation, SLA, and audit paths are made singular and authoritative.
