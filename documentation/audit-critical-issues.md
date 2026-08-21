# Critical Issues Audit

Repository: `nvnkmr127/privyr-1`

Audited ref: `e47d3de0f48897b5fb8205edf9f7d4a56263951f`

Scope: Lead Operations only. No application fixes were made.

## P0. Release blockers

### P0-001. PHP syntax error in Lead status endpoint

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`

Line: 623-648

Function: `updateStatus()`

Finding: Two identical `catch (\\Exception $exception)` clauses are consecutive.

Expected: A single catch block must handle the lifecycle exception.

Impact: PHP cannot parse the controller correctly. This blocks controller execution and is a production release blocker.

Severity: P0

Recommended Fix: Remove the duplicate catch, add a status endpoint test for success and failure, then run the full Pest suite.

### P0-002. Inbox swipe mutation lacks object-level authorization

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`

Line: 143-194

Function: `swipeAction()`

Finding: The method reads `lead_id` directly from the request and updates the Lead. It does not call `authorize()` or a LeadVisibilityService check before mutation.

Expected: Every Lead mutation must prove access to the target Lead using the same policy used by Lead view/update/delete.

Impact: A user may attempt to mutate another user's Lead by replacing the Lead ID. Route ACL alone is not object-level access control.

Severity: P0 pending verification with an authenticated non-owner test.

Recommended Fix: Delegate swipe actions to a policy-aware Lead operation service. Reject any Lead outside the caller's visibility scope.

### P0-003. Legacy Inbox bulk mutation lacks per-record policy enforcement

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`

Line: 199-260

Function: `bulkAction()`

Finding: Direct `whereIn('id', $leadIds)->update(...)` calls modify arbitrary Lead IDs supplied by the client. Reassignment and stage changes also use direct model updates.

Expected: Bulk actions must resolve the requested IDs through LeadVisibilityService, then call the same domain services as single-record mutations.

Impact: Potential cross-owner modification, skipped stage history, skipped lifecycle rules, skipped automation side effects, and incomplete audit trails.

Severity: P0 pending authorization test.

Recommended Fix: Remove this legacy mutation path or route it through `BulkLeadOperationService` with policy checks and per-action service dispatch.

## P1. Security and data integrity

### P1-001. SLA storage dependency is unverified

File: `packages/Webkul/Admin/src/DataGrids/Lead/LeadDataGrid.php`

Line: 145-163

Finding: LeadDataGrid executes four correlated SQL subqueries against `lead_slas`.

Expected: `lead_slas` must have a migration, model, service, scheduler, writers, indexes, and tests.

Impact: Lead listing or export may fail at runtime if the table is absent. Even if created outside the Lead package, the lifecycle remains unverified.

Severity: P1

Recommended Fix: Verify schema and producers. Complete SLA lifecycle before treating SLA metrics as implemented.

### P1-002. Facebook signature validation accepts missing signatures

File: `packages/Webkul/Lead/src/Services/LeadCaptureService.php`

Line: 225-242

Function: `assertValidFacebookSignature()`

Finding: Missing secret, missing signature, or missing raw body returns without rejection.

Expected: Configured signed endpoints must reject unsigned requests.

Impact: Provider authenticity is optional rather than enforced.

Severity: P1

Recommended Fix: Make verification mode explicit per connector and reject missing signatures whenever signed delivery is required.

### P1-003. Public connector replay protection is missing

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/PublicLeadCaptureController.php`

Line: 18-115 plus provider handlers

Finding: Connector tokens identify the destination, but no common timestamp, nonce, event ID, or replay window is enforced.

Expected: Each provider delivery must be idempotent and replay-safe.

Impact: Replayed requests may create duplicate processing attempts or repeatedly trigger downstream actions.

Severity: P1

Recommended Fix: Persist a provider event key and enforce uniqueness. Require timestamp/signature for providers that support signed envelopes.

### P1-004. Duplicate detection is split into two engines

Files:
`packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`
`packages/Webkul/Lead/src/Services/LeadIngestionService.php`
`packages/Webkul/Lead/src/Services/LeadDuplicateService.php`
`packages/Webkul/Lead/src/Services/DuplicateMatchingService.php`

Finding: Manual flows use `LeadDuplicateService`, while canonical ingestion calls `DuplicateMatchingService`. The services do not use identical matching criteria.

Expected: One duplicate domain service should own normalization and matching rules.

Impact: A Lead may be considered a duplicate by one entry point and unique by another.

Severity: P1

Recommended Fix: Consolidate matching into one service and expose explicit policies for phone, email, external ID, and merge suggestions.

### P1-005. Create-after event may fire twice

Files:
`packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`, lines 367-420
`packages/Webkul/Lead/src/Services/LeadIngestionService.php`, lines 99-132
`packages/Webkul/Lead/src/Repositories/LeadRepository.php`

Finding: Repository creation already dispatches a Lead create-after event. Higher-level controller/ingestion code dispatches it again in paths that reach those lines.

Expected: Exactly one authoritative create-after event for a successful Lead create.

Impact: Duplicate workflows, duplicate notifications, duplicate audit consumers, or duplicate integration calls.

Severity: P1

Recommended Fix: Choose one event boundary, preferably the application service/repository boundary, and remove duplicate dispatches.

### P1-006. Lifecycle service has incomplete dependency injection

File: `packages/Webkul/Lead/src/Services/LeadLifecycleService.php`

Line: 20-175

Finding: `convertLead`, `markLost`, and `markJunk` reference `$this->leadNurtureService` and `$this->leadFollowUpService`, but those properties are not declared in the constructor shown in the implementation.

Expected: Every service dependency used by runtime methods must be injected and covered by tests.

Impact: Calling those methods may produce undefined property errors.

Severity: P1

Recommended Fix: Inject required services and create tests for converting, losing, and junking a Lead from every relevant starting state.

### P1-007. Nurture completion accepts arbitrary status values

File: `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`
Lines: approximately 673-704

File: `packages/Webkul/Lead/src/Services/LeadNurtureService.php`

Finding: Controller validates `outcome` as a string, and service writes it to `leads.status` without checking it against the Lead lifecycle status set.

Expected: Nurture outcomes must be an explicit enum or allowlisted set.

Impact: Invalid Lead lifecycle values may enter the database.

Severity: P1

Recommended Fix: Validate against `LeadLifecycleService::getValidStatuses()` and enforce transition rules.

### P1-008. Bulk qualification falls back to direct database mutation

File: `packages/Webkul/Lead/src/Services/BulkLeadOperationService.php`

Line: 207-215

Finding: The code checks for `changeQualificationStatus`. If absent, it directly updates `qualification_status` through `LeadRepository`.

Expected: Qualification must always use `LeadQualificationService`, which records history, events, and scoring.

Impact: Bulk qualification may bypass validation, history, and scoring behavior.

Severity: P1

Recommended Fix: Add a first-class bulk qualification service operation and remove the direct field fallback.

### P1-009. Rule-based assignment bypasses Lead persistence events

File: `packages/Webkul/Lead/src/Services/LeadAssignmentService.php`

Line: 245-280

Function: `performAssignment()`

Finding: The service writes `user_id` and `group_id` using `DB::table('leads')->update()`.

Expected: Assignment changes should pass through one business service that owns persistence and downstream event behavior.

Impact: Eloquent observers and any update-based auditing are bypassed.

Severity: P1

Recommended Fix: Use one repository/service mutation boundary with explicit event dispatch, while preventing recursion at the service level instead of bypassing all model events.

### P1-010. Follow-up cancellation is represented as completion

File: `packages/Webkul/Lead/src/Services/LeadFollowUpService.php`

Line: 160-183

Finding: `cancelOpenFollowUps()` sets `status` to `completed` and `is_done` to `1`, then appends a cancellation message to the comment.

Expected: Cancelled follow-ups should have a distinct state from completed follow-ups.

Impact: Analytics, SLA metrics, completion rates, and audit histories become inaccurate.

Severity: P1

Recommended Fix: Introduce a distinct `cancelled` state and track cancellation metadata.

### P1-011. Follow-up completion contains debug output

File: `packages/Webkul/Lead/src/Services/LeadFollowUpService.php`

Line: 119-151

Finding: `dump()` is executed during follow-up completion.

Expected: Production services should not emit debugging output during normal requests.

Impact: Polluted logs/responses and unpredictable UI behavior.

Severity: P1

Recommended Fix: Remove debug output and replace with structured logging when diagnostic information is required.

## P2. Functional completeness and architecture

### P2-001. Lead analytics contains explicit mocked metrics

File: `packages/Webkul/Lead/src/Services/LeadAnalyticsService.php`

Line: 25-48

Finding: Average aging, score distribution, and average response time return zeros/empty buckets with comments saying they are mocked or omitted.

Expected: Metrics shown as available must be backed by real calculations.

Impact: Management analytics provide misleading numbers.

Severity: P2

Recommended Fix: Implement real metrics or hide them until implemented.

### P2-002. Lead field audit uses `getDirty()` inside the updated event

File: `packages/Webkul/Lead/src/Listeners/LeadAuditSubscriber.php`

Line: 25-51

Finding: The subscriber iterates `getDirty()` inside an Eloquent `updated` listener.

Expected: Post-update change auditing should use a post-update change set such as `getChanges()` or capture originals before save.

Impact: Field-level audit rows may be incomplete or empty after updates.

Severity: P2

Recommended Fix: Use `getChanges()` and add tests for every audited field.

### P2-003. Lead export uses correlated EAV subqueries

File: `packages/Webkul/Admin/src/DataGrids/Lead/LeadDataGrid.php`

Line: 100-145

Finding: Every exported custom attribute is loaded through a correlated subquery.

Expected: Export should use a set-based query or batch extraction strategy.

Impact: Export latency and database load scale poorly with many Leads and attributes.

Severity: P2

Recommended Fix: Preload EAV values in one grouped query or build an export projection table.

### P2-004. Lead model follow-up accessors create repeated queries

File: `packages/Webkul/Lead/src/Models/Lead.php`

Functions: `getNextFollowUpAttribute`, `getLastContactedAttribute`, `getFollowUpCountAttribute`, `getCompletedFollowUpsAttribute`, `getOverdueFollowUpsAttribute`

Finding: Each accessor issues its own Activity query.

Expected: Collection views should use eager-loaded aggregates.

Impact: Lead list serialization may create N+1 query patterns.

Severity: P2

Recommended Fix: Add query scopes / `withCount` / eager aggregates and use them in grids and resources.

### P2-005. Assignment rule condition language is limited

File: `packages/Webkul/Lead/src/Services/LeadAssignmentService.php`

Line: 120-143

Finding: Supported condition operators are equals, contains, greater than, less than, and not equals. There is no compound AND/OR tree or richer type-aware matching.

Expected: Assignment rules should support the product's required targeting complexity.

Impact: Rule builders become hard to express and users need multiple overlapping rules.

Severity: P2

Recommended Fix: Introduce a validated condition tree and evaluate it through one shared rules engine.

### P2-006. Field-level Lead permissions are absent

Finding: LeadPolicy protects whole records. No equivalent field-level read/write authorization layer was verified for sensitive Lead fields.

Expected: Sensitive custom attributes and operational fields should have optional field-level restrictions.

Impact: Users with Lead edit access may modify more fields than intended.

Severity: P2

Recommended Fix: Add field permissions to role configuration and enforce them in request validation and serialization.

### P2-007. Legacy Contact/Quote/Product dependencies remain inside Lead scope

Files:
`packages/Webkul/Lead/src/Database/Migrations/2021_04_22_164215_create_leads_table.php`
`packages/Webkul/Admin/src/Http/Requests/LeadForm.php`
`packages/Webkul/Admin/src/Http/Controllers/Lead/QuoteController.php`
`packages/Webkul/Automation/src/Helpers/Entity/Person.php`
`packages/Webkul/Automation/src/Helpers/Entity/Quote.php`

Finding: The new product boundary is Lead-only, but Contact/Person/Organization and Quote/Product code remains in the runtime graph.

Expected: The Lead product should not require or expose those concepts.

Impact: Product complexity, database coupling, permissions, and maintenance burden remain high.

Severity: P2

Recommended Fix: Complete dependency map, migrate required data to Lead-owned fields, then remove obsolete packages and routes in a separate cleanup phase.

## P3. Cleanup

### P3-001. Duplicate import statement

File: `packages/Webkul/Lead/src/Services/LeadIngestionService.php`

Line: 9-10

Finding: `LeadCaptureLog` is imported twice.

Severity: P3.

### P3-002. Documentation drift

The supplied docs still describe Persons, Organizations, Campaigns, and broader CRM concepts as first-class features, even though the new product scope excludes them. This documentation must be regenerated after the architecture cleanup.

Severity: P3.

### P3-003. Multiple bulk implementations

The repository has the legacy Inbox bulk path and the newer `BulkLeadOperationService`. Consolidation should follow functional stabilization.

Severity: P3.

### P3-004. Multiple audit/event layers

`LeadObserver`, `LeadAuditSubscriber`, repository events, and domain service events overlap. This needs a clear ownership model.

Severity: P3.

## Risk ordering

1. Runtime parser failure
2. Object-level Lead authorization
3. Webhook authenticity and replay protection
4. SLA runtime dependency
5. Duplicate event dispatch
6. Duplicate detection consolidation
7. Lifecycle dependency repair
8. Nurture status enforcement
9. Bulk mutation consolidation
10. Assignment persistence consistency
11. Follow-up state machine
12. Automation idempotency
13. Analytics truthfulness
14. Scope cleanup
15. Performance and refactoring

## Release gate

The system should not be marked production-ready until all P0 issues are closed, P1 security/data integrity issues have explicit tests, and every Lead entry point passes the same canonical flow.
