# Full Codebase Audit

## 1. Executive Summary

Repository: `nvnkmr127/privyr-1`

Audited branch: `main`

Audited commit: `84f2347fde7a12d0f945aa76eb5f2adcc3c17c9b`

Audit branch: `audit/full-codebase-audit-2026-08-21`

Product boundary used: Lead Operations only.

The repository contains a substantial Lead Operations layer built on a modular Laravel 12 architecture. The current code has centralized ingestion, duplicate detection, assignment rules, lifecycle services, qualification, scoring, nurturing, follow-ups, attribution, analytics, imports, webhooks, audit logging, ACL and lead visibility. The system is not incomplete because core modules are absent. It is incomplete because several paths implement the same business operation differently.

The highest-risk issues are:

1. External ingestion creates Leads with an invalid lifecycle default. `LeadIngestionService::normalizeData()` does not establish a lifecycle status, while `LeadRepository::create()` falls back to `Active`. The lifecycle service defines `Open`, `Working`, `Nurturing`, `Converted`, `Lost`, and `Junk`. This makes externally captured Leads diverge from manually created Leads.
2. Nurturing writes `status` through `LeadRepository::update()`, while `LeadRepository::update()` explicitly removes `status` before persistence. The user receives a successful nurture response while the status transition is blocked by the repository guard.
3. Duplicate detection is not concurrency safe. The application checks for an existing Lead and then creates one, but the duplicate migration adds indexes rather than uniqueness constraints for normalized phone/email or origin plus external ID.
4. Generic public capture uses a shared configured secret and permits the secret through the query string. The same controller logs the full inbound payload and returns raw exception messages in HTTP 500 responses.
5. WhatsApp chat import invents `9999999999` when no phone is found. This creates fabricated Lead identity data and undermines duplicate detection and data quality.
6. The manual Lead UI and external Lead ingestion use different entry paths. The external path performs normalization, centralized validation, ingestion logging, attribution and duplicate matching. Manual creation uses a warning-oriented duplicate check and then calls `LeadRepository::create()` directly.
7. SLA capability is only partially wired. `EvaluateLeadSlasJob` and `SlaEvaluatorService` exist, but the scheduler registers other Lead commands and does not register an SLA evaluation command, and no dispatch call for `EvaluateLeadSlasJob` was found during the source search.
8. Automation has a useful execution job with retry, a persisted execution record and an idempotency key, but the idempotency key is only workflow plus entity plus event. Repeated legitimate executions of the same workflow and event for the same Lead are therefore intentionally collapsed unless another differentiator exists.
9. Lead visibility is protected at the policy layer for view, update and delete, and the Inbox swipe action also calls `authorize('update', $lead)`. However, `LeadVisibilityService` accepts an action argument and does not use it, so operation-specific visibility rules are not implemented in that service.
10. Product documentation is still inconsistent with the Lead-only direction. The supplied documents describe Persons, Organizations, Contact lookups, Campaigns and Marketing modules. The repository still contains Contact-oriented documentation and Lead views, although migrations show an active transition away from Contact tables.

Overall readiness: RED.

The codebase is a strong implementation foundation, but the Lead Operations path should not be treated as production-complete until lifecycle consistency, duplicate concurrency, public ingestion security, SLA wiring, and Contact removal dependencies are resolved and tested.

## 2. Audit Method

The audit compared the actual repository source against the supplied product specification and architecture documents. File existence, route existence, model existence and migration existence were not accepted as proof of completeness.

The audit traced user and integration inputs into controllers, requests, services, repositories, models, events, jobs, persistence and response behavior.

No application code was modified during the audit. Only the three requested Markdown audit files were written to the audit branch.

Runtime execution was not performed through the connected GitHub source. Therefore this report does not claim a passing or failing PHPUnit, Pest, browser, build or static-analysis run. The repository contains Pest, PHPUnit and Laravel Pint development dependencies, and CI workflow files exist. These were treated as available infrastructure, not as proof of runtime success.

## 3. Current Architecture

The application is a Laravel 12 project using package-based modules under `packages/Webkul`. Composer registers Lead, Activity, Admin, Attribute, DataTransfer, Email, EmailTemplate, Marketing, Tag, User, WebForm and Automation packages.

Evidence:
- `composer.json`, require and autoload sections.
- PHP `^8.3` and Laravel `^12.0`.

Main architecture:

```text
Admin UI
  -> LeadController
  -> LeadForm / request validation
  -> LeadRepository
  -> EAV AttributeValueRepository
  -> LeadObserver / activity logging

External lead sources
  -> public capture controllers
  -> LeadCaptureService / LeadIngestionService
  -> normalization and validation
  -> duplicate matching
  -> attribution
  -> LeadRepository
  -> assignment / scoring / data quality

Lead domain
  -> Lifecycle
  -> Qualification
  -> Nurture
  -> Follow-up
  -> Assignment
  -> SLA
  -> Automation
  -> Analytics
  -> Audit
  -> Visibility / ACL
```

The architecture has good separation at module level, but Lead business rules still live in Controllers, Repositories, Observers and Services at the same time.

## 4. Current Lead Workflow

### 4.1 Manual creation

Path:

```text
POST /admin/leads/create
  -> LeadForm
  -> LeadController::store
  -> LeadDuplicateService warning check
  -> pipeline and stage selection
  -> LeadRepository::create
```

Evidence:
- `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`, around lines 300-390.

Observation: this path does not enter `LeadIngestionService`, so ingestion logging, centralized attribution mapping and centralized ingestion validation are not shared with external capture.

### 4.2 External capture

Path:

```text
Public webhook or API
  -> LeadCaptureController / PublicLeadCaptureController
  -> LeadIngestionPayload
  -> LeadIngestionService::ingest
  -> normalization
  -> data quality validation
  -> duplicate matching
  -> attribution
  -> LeadRepository::create or update
  -> assignment / score / data quality
```

Evidence:
- `app/Http/Controllers/Api/LeadCaptureController.php`, around lines 40-140.
- `packages/Webkul/Lead/src/Services/LeadIngestionService.php`, complete implementation.

### 4.3 Manual lifecycle change

Path:

```text
PUT /admin/leads/status/edit/{id}
  -> LeadController::updateStatus
  -> LeadLifecycleService::changeStatus or reopenLead
  -> status history
  -> lifecycle events
```

Evidence:
- `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`, around lines 545-585.
- `packages/Webkul/Lead/src/Services/LeadLifecycleService.php`.

This path is structurally correct.

### 4.4 Nurturing

Path:

```text
POST /admin/leads/nurture/{id}
  -> LeadNurtureService::startNurturing
  -> LeadRepository::update
  -> nurture history
  -> system activity
```

The implementation has a critical service contract conflict because `LeadRepository::update()` strips the `status` field before persistence.

Evidence:
- `packages/Webkul/Lead/src/Services/LeadNurtureService.php`, around lines 20-125.
- `packages/Webkul/Lead/src/Repositories/LeadRepository.php`, around lines 200-245.

## 5. Lead Core Audit

### Creation
Status: ORANGE

Backend exists and works through the repository. Manual and external capture use different paths and therefore different business behavior.

### Editing
Status: YELLOW

Controller authorization exists. Repository update centralizes EAV, scoring and data quality. However lifecycle fields are intentionally stripped in the repository, which conflicts with services that try to update them through the repository.

### Viewing
Status: GREEN

Lead view calls `authorize('view', $lead)`.

### Archive
Status: YELLOW

Archive uses a boolean flag and Inbox filtering. A dedicated archive history and restore workflow is not evident as a first-class domain operation.

### Permanent delete
Status: YELLOW

Deletion exists. The product specification requires dependency handling and historical preservation. Current code does not establish a complete immutable retention model.

### Search, filters, pagination
Status: GREEN

DataGrid, Kanban and Inbox provide multiple filtered views. Large-data performance still needs runtime validation.

### Kanban
Status: YELLOW

Stage changes are present and LeadObserver writes stage history. The system still has multiple stage mutation paths, so execution consistency should be enforced through one domain service.

### Bulk actions
Status: YELLOW

Bulk operations filter authorized records in several controller methods, but not all actions share one business service. Stage, status, archive, ownership and deletion need one consistent authorization and side-effect contract.

## 6. Lifecycle Audit

Supported lifecycle constants in `LeadLifecycleService`:
- Open
- Working
- Nurturing
- Converted
- Lost
- Junk

Reopen returns Lost or Junk Leads to Working.

Critical finding:

`LeadRepository::create()` defaults status to `Active` when no status is supplied. `LeadIngestionService::normalizeData()` does not set a lifecycle status. Therefore external ingestion may create a Lead with `Active`, which is outside the lifecycle service's accepted statuses.

Evidence:
- `packages/Webkul/Lead/src/Repositories/LeadRepository.php`, create method around lines 110-145.
- `packages/Webkul/Lead/src/Services/LeadIngestionService.php`, `normalizeData()`.
- `packages/Webkul/Lead/src/Services/LeadLifecycleService.php`, status constants and validation.

Severity: P0.

Expected: every Lead enters a valid lifecycle state through one canonical lifecycle definition.

## 7. Qualification Audit

Qualification models, repositories, service events and UI endpoints exist.

Status: YELLOW.

The code has formal qualification history and events, but qualification writes are also embedded in `LeadRepository::create()` and `update()`. This makes the repository a second qualification orchestration layer instead of a persistence layer.

Evidence:
- `packages/Webkul/Lead/src/Repositories/LeadRepository.php`.
- `packages/Webkul/Admin/src/Http/Controllers/Lead/QualificationController.php`.
- Lead qualification events in repository and lifecycle services.

## 8. Pipeline and Stage Audit

Pipelines and stages have CRUD and Kanban support.

Stage history is recorded by `LeadObserver` when `lead_pipeline_stage_id` changes. `stage_changed_at` is also maintained there.

Evidence:
- `packages/Webkul/Lead/src/Observers/LeadObserver.php`.

Status: YELLOW.

Main gap: several paths still modify stage directly, while business requirements need one stage transition boundary that guarantees history, validation, automation and SLA recalculation.

## 9. Assignment Audit

Implemented capabilities:
- manual assignment
- unassignment
- team assignment
- direct rules
- round robin
- weighted distribution
- least assigned
- capacity based distribution
- assignment history
- fallback behavior

Evidence:
- `packages/Webkul/Lead/src/Services/LeadAssignmentService.php`.

Status: YELLOW.

Issues:
- rule evaluation loads all active rules for each assignment operation.
- manual assignment validation only checks integer shape at the controller layer, not target existence before service execution.
- when no assignment rule matches, the current code intentionally leaves Leads unassigned. This contradicts the requirement for a reliable fallback assignment strategy.

Severity: P1.

## 10. Follow-up Audit

Lead follow-up services, Inbox states and scheduled commands exist.

Scheduler evidence:
- `routes/console.php` registers `lead:process-follow-ups` every five minutes.

Status: YELLOW.

Follow-up is currently represented through activity records plus convenience Lead fields such as `next_follow_up_at`. The product specification expects a complete follow-up lifecycle with history, notifications, cancellation, rescheduling and SLA linkage. The existing implementation should be treated as a functional foundation, not a final domain contract.

## 11. Nurture Audit

Nurture tables, models, services, jobs, DataGrid and UI exist.

Critical bug:

`LeadNurtureService::startNurturing()` passes `'status' => 'Nurturing'` to `LeadRepository::update()`, but `LeadRepository::update()` unsets `status` before calling the parent update. The same conflict occurs in `completeNurturing()`.

Result: nurture history and system activity may be recorded while the Lead lifecycle status remains unchanged.

Severity: P0.

Recommended fix: status changes must go through `LeadLifecycleService`, not the generic repository update method.

## 12. Activity and Communication Audit

Activity module exists with Lead-linked Notes, Calls, Meetings, Emails and system activities.

Status: GREEN for core activity logging.

Communication abstraction is only partially complete. Email functionality exists, but the specification requires future provider readiness, external message IDs and message-level idempotency. These controls are not uniformly represented across every channel.

Status: YELLOW.

## 13. Attribution Audit

Lead model and ingestion service contain source, origin, campaign, medium, content, term, landing page, form and first/latest touch fields. Attribution history services also exist.

Status: GREEN for storage and core ingestion mapping.

Gap: manual UI creation does not share the same canonical attribution flow as external ingestion.

Severity: P2.

## 14. Duplicate Audit

There are at least two duplicate engines:
- `LeadDuplicateService` used in manual Lead creation.
- `DuplicateMatchingService` used by `LeadIngestionService`.

This is a material architecture split because the matching rules are not guaranteed to be identical.

`LeadDuplicateService::detect()` also accepts nested `person` payloads and passes `person.contact_numbers` directly into `normalizePhone(?string)`, which expects a string. A nested array therefore creates a type error path.

Evidence:
- `packages/Webkul/Lead/src/Services/LeadDuplicateService.php`, detect method.

Concurrency gap:

Migration `2026_08_18_182409_add_duplicate_fields_to_leads_table.php` adds indexes for normalized phone and email and foreign keys for merge relationships, but does not add uniqueness constraints for primary normalized contact values or origin plus external ID.

Expected: duplicate checking must have a database-backed concurrency guard.

Severity: P0 for concurrent duplicate creation.

## 15. Automation Audit

Automation listener and queue execution exist.

Evidence:
- `packages/Webkul/Automation/src/Listeners/Entity.php`.
- `packages/Webkul/Automation/src/Jobs/ExecuteWorkflowActionJob.php`.

Positive implementation details:
- queue execution
- three retry attempts
- persisted workflow execution record
- idempotency key
- execution depth guard

Remaining gaps:
- idempotency key uses workflow ID, entity ID and event name only.
- execution depth is static process state, not a durable workflow chain identifier.
- complete loop prevention for cross-job workflow chains is not established.
- workflow action authorization is not independently enforced at execution time in the inspected job.

Status: YELLOW.

Severity: P1.

## 16. SLA Audit

`EvaluateLeadSlasJob` exists and calls `SlaEvaluatorService::evaluate()`.

Evidence:
- `packages/Webkul/Lead/src/Jobs/EvaluateLeadSlasJob.php`.
- `packages/Webkul/Lead/src/Services/Sla/SlaEvaluatorService.php`.

However, `routes/console.php` schedules:
- inbound email processing
- Meta polling
- lead sequences
- lead follow-ups
- lead health evaluation

No SLA scheduler registration was found, and a repository search did not find `EvaluateLeadSlasJob::dispatch`.

Status: RED.

Severity: P1.

Expected: SLA evaluation must run on a predictable schedule and write durable due-soon, breach, resolution and escalation history.

## 17. Data Quality Audit

`LeadDataQualityService` normalizes names, emails and phones and calculates a data quality state.

Status: YELLOW.

Important inconsistency: external ingestion explicitly calls the service. Manual creation relies primarily on `LeadForm` plus repository behavior. A single authoritative validation pipeline should exist for every Lead write path.

The phone normalizer strips everything except digits and an optional leading plus. It is deterministic, but not country-aware. This is acceptable as a normalization base, not as a complete phone identity model.

## 18. Analytics and Dashboard Audit

The analytics facade provides total Leads, qualification, won/lost, conversion rate, overdue follow-ups, stale Leads, source, pipeline and owner metrics.

Evidence:
- `packages/Webkul/Lead/src/Services/LeadAnalyticsService.php`.

Status: YELLOW.

Required metrics still need direct verification and dedicated tests for:
- team performance
- stage aging
- nurture metrics
- follow-up metrics
- SLA metrics
- lost reasons
- attribution performance across first and latest touch

The facade injects `LeadTrendAnalyticsService` but does not return trend metrics in the shown method, indicating partial integration.

## 19. API Audit

Routes include public lead capture endpoints and authenticated analytics/activity/template endpoints.

Evidence:
- `routes/api.php`.

Status: YELLOW.

Security findings:

1. Generic capture secret is accepted from header or query parameter.
2. Full request payload is logged at info level.
3. Raw exception messages are included in HTTP 500 responses.
4. Generic capture does not show a common event ID replay ledger.
5. WhatsApp import uses a fabricated fallback phone number.

Evidence:
- `app/Http/Controllers/Api/LeadCaptureController.php`, capture method.

Severity: P1.

## 20. Webhook Audit

Facebook verification uses HMAC for the capture path. Generic providers use a configured shared secret. The code supports many provider shapes through one controller.

Status: YELLOW.

The central missing control is a uniform webhook delivery ledger with event ID, signature state, first-seen timestamp, processed timestamp and response status.

## 21. Permissions and Visibility Audit

`LeadPolicy` protects view, create, update and delete. `LeadVisibilityService` supports global, group and own visibility.

Evidence:
- `packages/Webkul/Lead/src/Policies/LeadPolicy.php`.
- `packages/Webkul/Lead/src/Services/LeadVisibilityService.php`.
- `packages/Webkul/Admin/src/Http/Middleware/Bouncer.php`.

Status: YELLOW.

The policy does use visibility checks, and Inbox swipe also calls `authorize('update', $lead)`. This is a positive security control.

Gap: `LeadVisibilityService` accepts an action argument but uses the same visibility calculation for view, edit and delete. Operation-specific visibility is therefore not implemented in the visibility service itself.

This should be reviewed against the intended business rule for own, team and all permitted Leads.

## 22. Audit Logging

The current main branch registers `LeadObserver` in `LeadServiceProvider`.

Evidence:
- `packages/Webkul/Lead/src/Providers/LeadServiceProvider.php`.
- `packages/Webkul/Lead/src/Observers/LeadObserver.php`.

The observer records system activities for stage, owner, qualification, score, expected close date, value and status changes, and records stage history.

Status: GREEN for the inspected Lead observer path.

Gap: direct query-builder updates and non-model persistence paths still need dedicated coverage to ensure audit history is not silently bypassed.

## 23. Person, Contact and Organization Cleanup

The repository is in a migration state.

Evidence of cleanup already underway:
- `2026_08_18_100055_move_contact_data_to_leads_table.php`
- `2026_08_18_100144_drop_contact_tables_and_foreign_keys.php`
- `2026_08_18_165926_drop_person_id_from_activity_participants.php`
- recent commit message: `Fix test suite errors, remove Person proxies, format code`

Remaining references include:
- `packages/Webkul/Admin/src/Resources/views/leads/common/contact.blade.php`
- Contact-related documentation
- `person_name`, `organization_name`, `emails`, `contact_numbers` naming in Lead model and services
- supplied documentation still describing Person and Organization entities

Status: ORANGE.

Expected: Lead should be self-contained, with Lead identity fields stored on the Lead and no Contact entity dependency in routes, models, permissions, menus, services or documentation.

Recommended order:
1. finish dependency graph
2. replace remaining Contact-oriented UI and terminology
3. remove obsolete permissions and menus
4. remove obsolete documentation
5. verify migrations and foreign keys
6. run full regression suite

## 24. Database Audit

Positive items:
- normalized contact fields indexed
- merge relationships have foreign keys
- Lead-specific history tables exist for multiple domains
- migrations are package-local and auto-loaded by LeadServiceProvider

Gaps:
- duplicate concurrency constraints are incomplete
- lifecycle state consistency is partly application-enforced rather than schema-backed
- transition migrations need final cleanup review after Contact removal
- Lead schema contains a mixture of legacy and current naming conventions

## 25. Architecture Debt

The most visible architecture debt is the amount of business orchestration inside `LeadRepository`.

The repository currently handles:
- normalization
- EAV saving
- events
- qualification history
- assignment history
- assignment execution
- scoring
- data quality
- conversion integration

This conflicts with the intended repository role described by the project architecture documents, which says repositories should primarily handle persistence while business logic is distributed through dedicated services. The documentation itself describes repositories as the persistence boundary and services as the home for complex logic.

Evidence:
- `packages/Webkul/Lead/src/Repositories/LeadRepository.php`.
- supplied architecture documentation.

Severity: P2, with higher risk when business rules are changed.

## 26. Dead, Duplicate and Unused Code

The repository contains duplicate or overlapping Lead concepts that require cleanup rather than blind deletion.

Examples:
- multiple duplicate engines
- manual capture path plus canonical ingestion path
- LeadRepository doing domain orchestration
- multiple Lead analytics service layers
- multiple integration capture controllers
- Contact-oriented views and docs after Contact table removal

Some search terms such as TODO/FIXME did not produce definitive code-level results through the repository search API, so no false count is claimed for those categories.

## 27. Build and Test Status

The repository declares Pest, PHPUnit and Laravel Pint in `composer.json` and has GitHub Actions workflow files.

Runtime execution was not performed through the connected source. Therefore:
- PHPUnit/Pest result: UNVERIFIED
- Browser test result: UNVERIFIED
- Static analysis result: UNVERIFIED
- Production build result: UNVERIFIED

Recommended verification order after P0 and P1 fixes:
1. composer validation and autoload
2. PHP syntax check
3. Pest/PHPUnit full suite
4. package-specific Lead tests
5. API integration tests
6. browser tests
7. queue and scheduler tests
8. import stress tests

## 28. P0 Issues

### P0-001. External ingestion creates invalid lifecycle status

File: `packages/Webkul/Lead/src/Repositories/LeadRepository.php`

Function: `create`

Evidence: create path defaults `status` to `Active`.

Expected: status must be one of the lifecycle service states.

Impact: Leads captured from external sources can enter an invalid lifecycle state and bypass lifecycle transition assumptions.

Recommended Fix: establish one canonical initial lifecycle status, ideally through `LeadLifecycleService` or a Lead creation service.

### P0-002. Nurture status transition is blocked by repository field stripping

Files:
- `packages/Webkul/Lead/src/Services/LeadNurtureService.php`
- `packages/Webkul/Lead/src/Repositories/LeadRepository.php`

Function: `startNurturing`, `completeNurturing`, `update`

Expected: nurture transition changes lifecycle status and history atomically.

Impact: UI may report success while status remains unchanged.

Recommended Fix: route nurture transitions through `LeadLifecycleService` and preserve repository as persistence support.

### P0-003. Concurrent duplicate creation has no database concurrency guard

File: `packages/Webkul/Lead/src/Database/Migrations/2026_08_18_182409_add_duplicate_fields_to_leads_table.php`

Expected: the duplicate identity defined by the product needs transactional or schema-level protection.

Impact: two simultaneous requests can both pass duplicate detection and create duplicate Leads.

Recommended Fix: add a durable uniqueness strategy compatible with null values and business rules, then retain a transaction-level duplicate claim.

## 29. P1 Issues

### P1-001. Public capture logs full request payloads

File: `app/Http/Controllers/Api/LeadCaptureController.php`

Function: `capture`

Evidence: `Log::info(..., $data)`.

Impact: personal and Lead data enters standard logs.

### P1-002. Public capture returns raw exception messages

File: same controller, catch block.

Impact: internal exception details are exposed to external clients.

### P1-003. Public capture supports query-string secrets

File: same controller.

Impact: URL based secrets are more likely to appear in logs and monitoring systems.

### P1-004. WhatsApp import fabricates a fallback phone number

File: same controller, `importWhatsAppChat`.

Impact: false identity data and duplicate collisions.

### P1-005. SLA evaluator is not visibly scheduled or dispatched

Files:
- `packages/Webkul/Lead/src/Jobs/EvaluateLeadSlasJob.php`
- `packages/Webkul/Lead/src/Services/Sla/SlaEvaluatorService.php`
- `routes/console.php`

Impact: SLA requirements exist in code but are not proven operational.

### P1-006. Duplicate matching engines are split

Files:
- `LeadDuplicateService.php`
- `DuplicateMatchingService.php`

Impact: inconsistent dedupe behavior by entry point.

### P1-007. Manual creation bypasses canonical ingestion pipeline

File: `LeadController::store`.

Impact: inconsistent normalization, attribution, ingestion logging and duplicate rules.

### P1-008. Assignment failure can leave Leads unassigned

File: `LeadAssignmentService::assignLead` and `LeadRepository::create`.

Impact: assignment SLA and action-center requirements are weakened.

## 30. P2 Issues

1. Repository contains too much business orchestration.
2. Analytics facade does not expose all injected analytics capabilities.
3. Contact terminology remains in Lead code and UI.
4. Archive and restore need a first-class operational contract.
5. Communication needs one message-level idempotency model.
6. Stage and status mutations need one canonical service boundary.
7. Large-data Kanban and Inbox need performance regression coverage.
8. Import processing should be partitioned into smaller queue units for safer retries.
9. Field permissions are not demonstrated as a complete Lead-field security layer.

## 31. P3 Issues

1. Documentation cleanup after Lead-only transition.
2. Naming consistency for person_name, organization_name and contact_numbers.
3. Remove legacy provider branches after connector migration is complete.
4. Consolidate repeated analytics service naming.

## 32. Recommended Fix Order

### Phase 1, data integrity and lifecycle
1. Fix initial lifecycle status for every Lead entry point.
2. Fix nurture status transitions through lifecycle service.
3. Add duplicate concurrency protection.
4. Add lifecycle transition tests.

### Phase 2, public ingestion security
5. Remove full payload logging or redact sensitive fields.
6. Stop returning raw exceptions.
7. Remove query-string secrets from the generic capture API.
8. Add provider event IDs, replay protection and delivery ledger.
9. Remove fabricated WhatsApp fallback values.

### Phase 3, canonical architecture
10. Make `LeadIngestionService` or a shared Lead creation service the common entry boundary.
11. Consolidate duplicate engines.
12. Reduce LeadRepository business logic.
13. Route all assignment, stage, lifecycle and nurture changes through domain services.

### Phase 4, SLA and automation
14. Wire SLA scheduling.
15. Add SLA history and escalation.
16. Strengthen workflow idempotency and durable loop prevention.
17. Add automation execution coverage.

### Phase 5, product cleanup
18. Complete Contact and Organization dependency removal.
19. Remove obsolete menus, permissions, routes and docs.
20. Rebuild the final Lead Operations documentation from the cleaned architecture.

## 33. Final Product Readiness Assessment

Current readiness: RED.

The product has enough code to become a strong Lead Operations system. The priority is not adding more modules. The priority is making every Lead mutation use one predictable business path, protecting duplicate identity at the database level, securing public ingestion, wiring SLA operations, and finishing the Contact removal.

The system should not be called feature-complete until the P0 issues are closed and the P1 matrix rows have passing automated coverage.

## 34. Evidence Index

- `composer.json`, PHP and Laravel requirements, package registration.
- `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`, Lead UI entry points and mutations.
- `packages/Webkul/Lead/src/Services/LeadIngestionService.php`, canonical external ingestion.
- `packages/Webkul/Lead/src/Repositories/LeadRepository.php`, Lead persistence and lifecycle conflict.
- `packages/Webkul/Lead/src/Services/LeadLifecycleService.php`, lifecycle states and transitions.
- `packages/Webkul/Lead/src/Services/LeadNurtureService.php`, nurture transition implementation.
- `packages/Webkul/Lead/src/Services/LeadAssignmentService.php`, assignment rule engine.
- `packages/Webkul/Lead/src/Services/LeadDuplicateService.php`, manual duplicate engine.
- `packages/Webkul/Lead/src/Database/Migrations/2026_08_18_182409_add_duplicate_fields_to_leads_table.php`, duplicate indexes and merge fields.
- `packages/Webkul/Lead/src/Jobs/EvaluateLeadSlasJob.php`, SLA job.
- `packages/Webkul/Lead/src/Services/Sla/SlaEvaluatorService.php`, SLA evaluator.
- `routes/console.php`, scheduler registrations.
- `packages/Webkul/Automation/src/Listeners/Entity.php`, workflow event consumer.
- `packages/Webkul/Automation/src/Jobs/ExecuteWorkflowActionJob.php`, retry and execution model.
- `packages/Webkul/Lead/src/Policies/LeadPolicy.php`, Lead ACL and visibility enforcement.
- `packages/Webkul/Lead/src/Services/LeadVisibilityService.php`, owner/group/global visibility.
- `packages/Webkul/Admin/src/Http/Middleware/Bouncer.php`, administrative ACL middleware.
- `app/Http/Controllers/Api/LeadCaptureController.php`, public capture security and provider adapters.
- `packages/Webkul/Lead/src/Observers/LeadObserver.php`, stage, owner, qualification, score, value and status logging.
- Contact cleanup migrations and remaining Contact-oriented UI/docs.

Audit conclusion: the codebase is a substantial Lead CRM foundation, but current behavior is inconsistent across entry points. Fix the P0 issues first, then enforce a single Lead Operations execution model across manual, import, API, webhook and automation paths.
