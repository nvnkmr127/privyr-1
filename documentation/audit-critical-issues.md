# Critical Issues Audit

Repository: `nvnkmr127/privyr-1`

Audited commit: `84f2347fde7a12d0f945aa76eb5f2adcc3c17c9b`

Audit branch: `audit/full-codebase-audit-2026-08-21`

Scope: Lead Operations only.

No application code was changed during the audit.

## P0. Release blockers

### P0-001. External Leads use an invalid lifecycle default

File: `packages/Webkul/Lead/src/Repositories/LeadRepository.php`

Function: `create()`

Evidence: the repository assigns `status` using `$data['status'] ?? 'Active'`.

Related file: `packages/Webkul/Lead/src/Services/LeadLifecycleService.php`

Expected: every Lead must enter a lifecycle status supported by the lifecycle service: Open, Working, Nurturing, Converted, Lost or Junk.

Impact: external API, webhook and import Leads may enter `Active`, while manual Leads explicitly enter Open. The product therefore has two incompatible initial states.

Recommended fix: define one canonical initial state in a Lead creation service and test every Lead entry point against the same invariant.

### P0-002. Nurture status changes are stripped before persistence

Files:
- `packages/Webkul/Lead/src/Services/LeadNurtureService.php`
- `packages/Webkul/Lead/src/Repositories/LeadRepository.php`

Functions: `startNurturing()`, `completeNurturing()`, `update()`

Evidence: nurture passes `status` to `LeadRepository::update()`. The repository explicitly removes `status` before calling the parent update.

Expected: starting or ending nurture must change the lifecycle status atomically and record the transition.

Impact: the system may create nurture history and activities while leaving the Lead in its previous status.

Recommended fix: route status changes through `LeadLifecycleService` and let the repository persist attributes rather than own lifecycle decisions.

### P0-003. Duplicate detection has no database concurrency guard

File: `packages/Webkul/Lead/src/Database/Migrations/2026_08_18_182409_add_duplicate_fields_to_leads_table.php`

Function: migration `up()`

Evidence: normalized email, normalized phone, duplicate state and merge references are indexed, but the migration does not establish a uniqueness rule for the business duplicate identity.

Expected: duplicate detection must remain correct when two matching requests arrive concurrently.

Impact: both requests may pass the application-level lookup before either transaction inserts the Lead.

Recommended fix: define a database-level uniqueness strategy compatible with null values and approved duplicate semantics, then retain the application duplicate matcher for warning and merge behavior.

## P1. Security and data integrity

### P1-001. Full Lead capture payloads are written to application logs

File: `app/Http/Controllers/Api/LeadCaptureController.php`

Function: `capture()`

Evidence: `Log::info(..., $data)` records the full inbound request payload.

Impact: Lead identity data and message content enter standard application logs.

Recommended fix: remove raw payload logging in production or use explicit redaction and a restricted debug channel.

### P1-002. Raw exception messages are returned from the public capture API

File: `app/Http/Controllers/Api/LeadCaptureController.php`

Function: `capture()` catch block

Evidence: the HTTP 500 response includes `$e->getMessage()`.

Impact: internal implementation details may be exposed to external callers.

Recommended fix: return a stable public error code and generic message. Keep detailed exception context server-side.

### P1-003. Generic capture accepts a secret in a query parameter

File: `app/Http/Controllers/Api/LeadCaptureController.php`

Function: `capture()`

Evidence: the controller reads `X-Capture-Secret` or the `key` query parameter.

Impact: query-string secrets are more likely to appear in reverse-proxy, load-balancer and monitoring logs.

Recommended fix: require the secret in an authentication header and replace the shared global secret model with connector-scoped credentials.

### P1-004. Public capture lacks a common replay ledger

Files:
- `routes/api.php`
- `app/Http/Controllers/Api/LeadCaptureController.php`
- public connector capture controllers

Expected: provider event ID, delivery ID or a generated idempotency key should be persisted before processing.

Impact: replayed provider deliveries can re-enter parsing and duplicate checks.

Recommended fix: add one webhook delivery ledger with connector ID, external event ID, signature status, first seen time, processing state and response status.

### P1-005. WhatsApp chat import fabricates a phone number

File: `app/Http/Controllers/Api/LeadCaptureController.php`

Function: `importWhatsAppChat()`

Evidence: when no phone is extracted, code defaults to `9999999999`.

Impact: fabricated identity data can create false duplicate matches and polluted Lead records.

Recommended fix: reject the import or store the contact as missing, never fabricate an identity field.

### P1-006. Duplicate engines are split

Files:
- `packages/Webkul/Lead/src/Services/LeadDuplicateService.php`
- `packages/Webkul/Lead/src/Services/DuplicateMatchingService.php`

Finding: manual Lead creation and canonical ingestion do not use the same duplicate implementation.

Impact: identical Lead data may receive different duplicate results depending on entry point.

Recommended fix: one duplicate engine, one normalization contract, one result schema.

### P1-007. Nested duplicate payload can violate the duplicate service type contract

File: `packages/Webkul/Lead/src/Services/LeadDuplicateService.php`

Function: `detect()`

Finding: `person.contact_numbers` is passed into `normalizePhone(?string)`. A nested array payload therefore does not match the method contract.

Impact: some integration payloads can fail during duplicate detection instead of returning a deterministic match result.

Recommended fix: normalize accepted input shapes before calling scalar normalization methods.

### P1-008. Manual Lead creation bypasses canonical ingestion

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`

Function: `store()`

Finding: manual creation checks duplicates and then calls `LeadRepository::create()` instead of entering `LeadIngestionService`.

Impact: manual, import, API and webhook Leads do not share one complete normalization, attribution and ingestion-log contract.

Recommended fix: introduce a shared Lead creation command/service with source-specific adapters feeding it.

### P1-009. SLA evaluation is not operationally wired

Files:
- `packages/Webkul/Lead/src/Jobs/EvaluateLeadSlasJob.php`
- `packages/Webkul/Lead/src/Services/Sla/SlaEvaluatorService.php`
- `routes/console.php`

Finding: the job and evaluator exist, but the scheduler registers other Lead commands and no `EvaluateLeadSlasJob::dispatch` was found in repository search.

Impact: SLA due-soon and breach states may never advance automatically.

Recommended fix: register a dedicated scheduled command or job dispatch and add scheduler integration tests.

### P1-010. Assignment fallback deliberately allows unassigned Leads

Files:
- `packages/Webkul/Lead/src/Services/LeadAssignmentService.php`
- `packages/Webkul/Lead/src/Repositories/LeadRepository.php`

Finding: `assignLead()` returns false when no active rule matches, and the create path accepts the no-match outcome.

Impact: assignment SLA and action-center reliability depend on manual recovery.

Recommended fix: define an explicit default queue or fallback assignment policy and record failures as operational events.

### P1-011. Lifecycle rules are split between service and repository

Files:
- `LeadLifecycleService.php`
- `LeadRepository.php`
- `LeadController.php`

Finding: repository update strips lifecycle fields while lifecycle/nurture services rely on repository update for those fields.

Impact: behavior depends on which service is used.

Recommended fix: make lifecycle service the only owner of lifecycle state transitions.

### P1-012. Workflow idempotency policy is too coarse

File: `packages/Webkul/Automation/src/Jobs/ExecuteWorkflowActionJob.php`

Finding: idempotency key is generated from workflow ID, entity ID and event name.

Impact: two legitimate repeated events with the same triple are treated as the same execution.

Recommended fix: define an event delivery ID or execution UUID and use it in the idempotency key. Keep a separate dedupe key for exact duplicate deliveries.

### P1-013. Workflow loop protection is process-local

File: `packages/Webkul/Automation/src/Jobs/ExecuteWorkflowActionJob.php`

Finding: execution depth is stored in a static PHP property.

Impact: queued workflow chains crossing worker boundaries are not governed by one durable execution chain ID.

Recommended fix: persist a workflow chain ID and depth in the execution record and pass it across jobs.

### P1-014. Merge service does not enforce its own ACL boundary

File: `packages/Webkul/Lead/src/Services/LeadMergeService.php`

Finding: the service comment explicitly leaves ACL checking to the controller.

Impact: future callers could reuse merge logic without the required policy check.

Recommended fix: enforce Lead visibility and merge permission at the service boundary as defense in depth, while retaining controller authorization.

## P2. Architecture and quality

### P2-001. LeadRepository is a domain orchestration layer

File: `packages/Webkul/Lead/src/Repositories/LeadRepository.php`

Finding: create/update perform qualification history, assignment, scoring, data quality, attribution-related work and event dispatching.

Impact: repository changes affect many domains and make alternate entry paths harder to reason about.

Recommended fix: move orchestration to explicit application/domain services and keep repository operations persistence-focused.

### P2-002. Analytics facade does not expose all injected analytics capabilities

File: `packages/Webkul/Lead/src/Services/LeadAnalyticsService.php`

Finding: `LeadTrendAnalyticsService` is injected but not returned by `getMetrics()`.

Impact: part of the analytics architecture is not connected to the facade used by the UI/API.

Recommended fix: either expose trend metrics through the public contract or remove the unused dependency.

### P2-003. Data quality is enforced through multiple layers

Files:
- `LeadDataQualityService.php`
- `LeadForm`
- `LeadRepository.php`

Impact: manual and external Leads may receive different validation guarantees.

Recommended fix: define a common Lead input contract and use source-specific adapters only for parsing.

### P2-004. Contact terminology remains after Contact table cleanup

Evidence:
- `packages/Webkul/Admin/src/Resources/views/leads/common/contact.blade.php`
- `person_name`, `organization_name`, `contact_numbers` in Lead model/services
- Contact documentation files

Impact: product terminology and code architecture remain inconsistent.

Recommended fix: finish dependency cleanup after verifying no runtime consumers remain.

### P2-005. Archive and restore need a first-class domain contract

Finding: archive state is represented by `is_archived` and Inbox behavior, but history, retention, restore permissions and downstream effects are not centralized.

Recommended fix: implement archive, restore and permanent delete as separate audited operations.

### P2-006. Import retry boundary is coarse

File: `packages/Webkul/Lead/src/Jobs/LeadIngestionBatch.php`

Finding: a single queued job processes every pending batch and every row inside each batch.

Impact: large imports create long job durations and broad retry scopes.

Recommended fix: dispatch smaller batch jobs and persist row-level checkpoints.

### P2-007. Phone normalization is deterministic but not country-aware

File: `LeadDataQualityService.php`

Finding: normalization strips punctuation and preserves plus, without country-specific parsing.

Impact: equivalent local and international numbers may remain distinct.

Recommended fix: define business phone normalization rules and test India-first and international formats.

## P3. Cleanup

### P3-001. Lead-only documentation rewrite

Supplied documentation still describes Persons, Organizations and Contact-linked Lead flows. The documentation should be rewritten after code cleanup so it describes the actual Lead-only product rather than the inherited Krayin model.

### P3-002. Remove obsolete integration branches after connector migration

Keep current provider adapters until connector-based ingestion coverage is proven. Do not delete them before migration tests exist.

## Immediate fix order

1. Initial lifecycle state.
2. Nurture lifecycle transition.
3. Duplicate concurrency protection.
4. Public payload log redaction.
5. Public exception sanitization.
6. Connector replay ledger.
7. Remove fabricated WhatsApp data.
8. SLA scheduler wiring.
9. Consolidate duplicate engines.
10. Make manual, import, API and webhook creation use one Lead creation contract.
11. Enforce service-level merge authorization.
12. Strengthen automation execution identity.
13. Complete Contact/Person/Organization dependency cleanup.
14. Run complete automated regression coverage.
