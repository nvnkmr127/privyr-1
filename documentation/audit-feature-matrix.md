# Lead Operations Audit Feature Matrix

Audited ref: `e47d3de0f48897b5fb8205edf9f7d4a56263951f`

This matrix groups closely related capability checks into traceable clusters. Every capability listed in the audit request is represented in one of the clusters below. A feature is marked Complete only when the full execution path, validation, authorization, persistence, side effects, and tests are sufficiently connected. The supplied audit instructions explicitly reject route/file/UI existence as proof of completion. fileciteturn0file0

Legend:

- GREEN = Complete
- YELLOW = Partial
- ORANGE = Incorrect or poorly connected
- RED = Missing or broken
- GRAY = Dead, unused, or outside the Lead-only product boundary

Backend, Frontend, Database, API, Events, Permissions, Tests, and Integrations columns use Y = verified present, P = partial, N = absent or unverified, O = incorrect/bypassed.

| Feature / capability cluster | Status | Backend | Frontend | Database | API | Events | Permissions | Tests | Integrations | Severity | Evidence | Recommended action |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---|---|---|
| Lead creation, editing, viewing | GREEN | Y | Y | Y | P | Y | Y | P | P | P2 | LeadController store/edit/view | Keep one canonical Lead mutation boundary and add end-to-end coverage |
| Lead archive, delete, restore | ORANGE | P | Y | P | P | P | Y | P | P | P1 | LeadController destroy, is_archived fields | Separate archive from permanent delete and add restore semantics |
| Lead search, filters, sorting, pagination | GREEN | Y | Y | Y | Y | N | Y | Y | N | P2 | LeadDataGrid, LeadController get/search | Add large dataset performance tests |
| Lead Kanban and stage movement | ORANGE | Y | Y | P | Y | Y | Y | P | P | P1 | LeadController updateStage/get, LeadObserver | Make every stage mutation use one service and history path |
| Lead bulk actions | ORANGE | Y | Y | Y | Y | O | O | P | P | P0 | LeadController bulkAction and BulkLeadOperationService | Delete legacy path and use policy-aware bulk service |
| Lead export | YELLOW | Y | Y | Y | Y | N | P | P | P | P2 | LeadDataGrid export/EAV subqueries | Confirm export ACL and optimize EAV extraction |
| CSV import, mapping, preview | YELLOW | Y | Y | Y | P | P | Y | Y | P | P2 | DataTransfer and Lead import tests | Make import use canonical ingestion path everywhere |
| Lifecycle Open, Working | GREEN | Y | Y | Y | Y | Y | Y | Y | P | P2 | LeadLifecycleService | Add full transition matrix tests |
| Lifecycle Nurturing | YELLOW | Y | Y | Y | Y | Y | P | Y | P | P1 | LeadNurtureService | Route through lifecycle service and validate state transitions |
| Lifecycle Converted, Lost, Junk | ORANGE | P | Y | Y | Y | Y | Y | P | P | P1 | LeadLifecycleService missing injected dependencies | Repair lifecycle orchestration and test every starting state |
| Lifecycle Reopen | GREEN | Y | Y | Y | Y | Y | Y | Y | P | P2 | LeadLifecycleService reopenLead | Keep allowlist and history tests |
| Qualification status and fields | GREEN | Y | Y | Y | P | Y | Y | Y | P | P2 | LeadQualificationService | Centralize status writes |
| Qualification score and scoring rules | YELLOW | Y | Y | Y | N | P | P | Y | N | P2 | LeadScoringEngine | Add recalculation events, throttling, and rule tests |
| Qualification history and validation | GREEN | Y | Y | Y | P | Y | Y | Y | P | P2 | LeadQualificationService | Preserve single qualification service |
| Pipelines and stages | GREEN | Y | Y | Y | Y | Y | Y | P | P | P2 | Lead pipeline repositories/controllers | Add destructive dependency checks |
| Stage ordering, change, history, aging | YELLOW | Y | Y | P | Y | Y | Y | P | P | P1 | LeadObserver, EvaluateLeadHealth | Complete durable stage aging and history semantics |
| Assignment owner and team | GREEN | Y | Y | Y | Y | Y | Y | Y | P | P2 | LeadAssignmentService | Centralize all assignments |
| Manual assignment and reassignment | GREEN | Y | Y | Y | Y | Y | P | Y | P | P2 | LeadAssignmentService | Validate target owner/team IDs and permissions |
| Automatic assignment and assignment rules | YELLOW | Y | Y | Y | P | Y | P | P | P | P1 | LeadAssignmentService | Add richer condition trees and execution audit |
| Round robin, weighted, least-assigned, capacity | YELLOW | Y | P | Y | N | Y | P | P | N | P1 | LeadAssignmentService executeRule | Add concurrency and fairness tests |
| Assignment history, failure, fallback | YELLOW | Y | P | Y | N | Y | P | P | P | P1 | LeadAssignmentRepository and performAssignment | Add failure records and explicit fallback outcomes |
| Follow-up create and edit | GREEN | Y | Y | Y | P | Y | P | Y | P | P2 | LeadFollowUpService | Add dedicated FollowUp domain abstraction |
| Follow-up complete | ORANGE | Y | Y | Y | P | Y | P | P | P | P1 | LeadFollowUpService complete | Remove dump and test activity state changes |
| Follow-up cancel and reschedule | YELLOW | Y | Y | Y | P | Y | P | P | P | P1 | LeadFollowUpService cancel/reschedule | Use distinct cancelled state and history |
| Follow-up overdue and next action | GREEN | Y | Y | Y | P | Y | P | Y | P | P2 | Lead model accessors and health command | Optimize query aggregation and scheduler coverage |
| Follow-up history and notifications | YELLOW | P | Y | P | P | P | P | P | P | P2 | Activity and LeadFollowUp services | Add first-class history and notification guarantees |
| Nurture entry, reason, notes | GREEN | Y | Y | Y | P | Y | P | Y | P | P2 | LeadNurtureService | Keep reason validation strict |
| Nurture re-engagement and history | YELLOW | Y | Y | Y | P | Y | P | Y | P | P1 | LeadNurtureService | Validate outcomes and connect to lifecycle history |
| Nurture automation | YELLOW | P | P | P | P | P | P | P | P | P1 | lead.nurturing.started/completed events | Add idempotent automation execution |
| Notes, calls, meetings, emails, follow-ups | GREEN | Y | Y | Y | P | Y | Y | Y | P | P2 | Activity routes/services | Verify channel-specific data rules |
| Activity timeline and system events | GREEN | Y | Y | Y | P | Y | Y | P | P | P2 | Lead activities + LeadObserver | Add timeline consistency tests |
| Activity history | YELLOW | P | Y | Y | P | P | P | P | P | P2 | Activity models and observers | Define immutable operational history requirements |
| Communication abstraction and outbound | YELLOW | Y | Y | P | Y | P | P | P | P | P2 | Email services, Lead emails | Standardize channel interface |
| Inbound, channel handling, external message IDs | YELLOW | P | P | P | P | P | P | P | P | P1 | Email/connector modules | Add provider event IDs and inbound processing model |
| Communication timeline and idempotency | YELLOW | P | Y | P | P | P | P | P | P | P1 | Lead events and external capture | Add message-level idempotency ledger |
| Future provider readiness | YELLOW | Y | P | P | Y | P | P | P | Y | P2 | Connector model/service | Keep provider adapters behind one contract |
| Source, origin, campaign, medium, content, term | GREEN | Y | Y | Y | Y | Y | P | Y | Y | P2 | LeadAttributionService | Add attribution validation rules |
| Form, landing page, first touch, latest touch | GREEN | Y | Y | Y | Y | Y | P | Y | Y | P2 | LeadAttributionService | Preserve immutable first-touch data |
| Attribution history | GREEN | Y | Y | Y | P | Y | P | Y | Y | P2 | LeadAttributionHistory | Add reporting consumers |
| Duplicate phone and email matching | GREEN | Y | Y | Y | P | P | P | Y | P | P1 | LeadDuplicateService / DuplicateMatchingService | Consolidate engines |
| Duplicate external ID and normalization | YELLOW | Y | P | P | Y | Y | P | Y | Y | P1 | LeadIngestionService normalization | Add database uniqueness and canonical normalization |
| Duplicate warnings | GREEN | Y | Y | P | Y | P | P | Y | P | P2 | LeadController store duplicate check | Restrict duplicate details to permitted users |
| Duplicate merging | YELLOW | Y | Y | Y | P | P | P | P | P | P1 | LeadMergeService and merge route | Enforce policy in controller/service and lock records |
| Merge history and permissions | YELLOW | Y | Y | Y | P | P | P | P | P | P1 | LeadMergeHistory | Add explicit merge permission and audit |
| Concurrent duplicate protection | RED | P | P | P | Y | P | P | N | N | P1 | No verified unique origin/external ID constraint | Add unique constraint and transactional duplicate claim |
| Automation triggers and conditions | YELLOW | Y | Y | Y | P | Y | Y | P | P | P1 | Automation package + Lead events | Define supported Lead event contract |
| Automation actions | YELLOW | Y | Y | Y | P | Y | P | P | P | P1 | Workflow actions | Test every action against Lead mutation rules |
| Scheduled automation and queue execution | YELLOW | P | P | P | P | P | P | P | P | P1 | Jobs/workflows, scheduler unverified | Verify queue and scheduler registration |
| Automation retry | YELLOW | P | P | P | P | P | P | P | P | P1 | Workflow/job infrastructure | Add retry policy and dead-letter handling |
| Automation idempotency and loop prevention | RED | P | P | P | P | P | P | N | P | P1 | No verified execution ledger/loop guard | Add durable execution keys and recursion guard |
| Automation execution logs and permissions | YELLOW | P | Y | Y | P | Y | Y | P | P | P2 | Workflow storage/ACL | Add complete execution audit model |
| Assignment SLA | RED | P | Y | N | P | P | Y | N | P | P1 | LeadDataGrid references lead_slas | Implement SLA record lifecycle |
| First Action SLA | RED | P | Y | N | P | P | Y | N | P | P1 | LeadDataGrid references lead_slas | Implement first action timer and breach handling |
| Follow-up SLA | RED | P | Y | N | P | P | Y | N | P | P1 | LeadDataGrid references lead_slas | Connect follow-up activity due times |
| Stage SLA | RED | P | Y | N | P | P | Y | N | P | P1 | LeadDataGrid references lead_slas | Connect stage history and SLA policy |
| Due soon, breach, resolution | RED | P | Y | N | P | P | Y | N | P | P1 | No verified SLA state engine | Add scheduler and state transitions |
| Escalation and SLA history | RED | P | Y | N | P | P | Y | N | P | P1 | No verified durable SLA history | Add escalation and immutable history |
| Validation and data quality state | GREEN | Y | Y | Y | Y | Y | P | Y | Y | P2 | LeadDataQualityService | Keep ingestion and manual rules aligned |
| Phone and email normalization | GREEN | Y | P | Y | Y | Y | P | Y | Y | P1 | DataQuality + Duplicate services | Make one normalizer authoritative |
| Custom attributes and required fields | YELLOW | Y | Y | Y | P | P | P | Y | P | P2 | LeadForm dynamic EAV validation | Add consistent required-field policy across entry points |
| Lead counts and core analytics | GREEN | Y | Y | Y | P | P | P | Y | P | P2 | LeadAnalyticsService | Add visibility-aware aggregation tests |
| Source and campaign performance | YELLOW | Y | Y | Y | P | P | P | Y | Y | P2 | SourceAnalytics + attribution | Complete campaign metric definitions |
| Owner, team, pipeline performance | GREEN | Y | Y | Y | P | P | Y | Y | P | P2 | Owner/Pipeline analytics services | Add ACL-scoped test fixtures |
| Stage aging and qualification metrics | YELLOW | Y | Y | P | P | P | P | P | P | P2 | LeadAnalytics + EvaluateLeadHealth | Replace derived/mock data with durable metrics |
| Conversion, lost, nurture, follow-up metrics | YELLOW | Y | Y | Y | P | P | P | P | P | P2 | Core analytics + lifecycle/nurture | Define metric truth sources |
| SLA metrics | RED | P | Y | N | P | P | Y | N | P | P1 | Missing/unverified SLA engine | Complete SLA engine before reporting |
| Action Center, Today's work, New Leads, Overdue | GREEN | Y | Y | Y | P | Y | Y | P | P | P2 | Lead inbox/dashboard code | Add runtime UI tests |
| Unassigned, Qualified, Nurturing, performance | YELLOW | Y | Y | Y | P | Y | Y | P | P | P2 | Lead Inbox/Nurture/Analytics | Ensure every widget uses same visibility scope |
| SLA breaches dashboard | RED | P | Y | N | P | P | Y | N | P | P1 | SLA backend missing/unverified | Wire after SLA engine is complete |
| CSV processing, mapping, preview, validation | GREEN | Y | Y | Y | Y | P | Y | Y | P | P2 | DataTransfer + LeadImport tests | Route all imports through ingestion service |
| Import duplicate detection and update existing | YELLOW | Y | Y | Y | Y | P | Y | Y | P | P1 | LeadImport/DataTransfer | Reuse canonical duplicate rules |
| Import error handling, batch, queue, history | YELLOW | Y | Y | Y | P | P | Y | Y | P | P2 | ImportWizard and tests | Verify async queue and durable history |
| API authentication and Lead ingestion | YELLOW | Y | P | P | Y | P | P | P | Y | P1 | LeadCaptureController and connector token | Standardize authentication scheme |
| API validation, idempotency, external IDs | YELLOW | Y | P | P | Y | Y | P | P | Y | P1 | LeadIngestionService | Add unique event constraints |
| API duplicate detection, errors, permissions, rate limiting | ORANGE | Y | P | P | Y | P | P | P | Y | P1 | Public/connector controllers | Add stable errors, ACL, and rate limits |
| Webhook authentication | YELLOW | Y | P | P | Y | P | P | P | Y | P1 | PublicLeadCaptureController | Unify signed/token verification |
| Webhook signature verification | ORANGE | P | N | P | Y | P | P | P | Y | P1 | Facebook signature method accepts missing data | Enforce per-provider signatures |
| Replay protection and idempotency | RED | P | N | N | Y | P | P | N | Y | P1 | No verified replay ledger | Add event IDs, timestamps, unique constraints |
| Webhook payload validation and Lead resolution | GREEN | Y | N | Y | Y | Y | P | P | Y | P2 | LeadCaptureService | Add schema validation per provider |
| Webhook error handling | YELLOW | Y | N | Y | Y | P | P | P | Y | P1 | PublicLeadCaptureController returns raw exception message | Sanitize external responses |
| Roles and base permissions | GREEN | Y | Y | Y | Y | Y | Y | P | P | P2 | Bouncer and Role package | Keep ACL inventory synchronized |
| Lead visibility, own/team/all permitted | GREEN | Y | Y | Y | P | Y | Y | Y | P | P1 | LeadVisibilityService + LeadPolicy | Apply policy consistently to every route |
| Field permissions | RED | P | P | P | P | N | P | N | P | P2 | No verified field ACL layer | Add field read/write permissions |
| API and automation permissions | YELLOW | Y | Y | Y | Y | Y | Y | P | P | P2 | ACL + routes | Add per-operation permission tests |
| Import/export permissions | YELLOW | Y | Y | Y | Y | P | P | P | P | P2 | DataGrid/import controllers | Verify export/import ACL independently |
| Lead change audit | YELLOW | Y | Y | Y | P | P | P | P | P | P2 | LeadAuditSubscriber + Observer | Define one authoritative audit layer |
| Field change audit | ORANGE | P | Y | Y | P | P | P | P | P | P2 | LeadAuditSubscriber uses getDirty in updated | Switch to post-update change set and test |
| Assignment, lifecycle, qualification audit | GREEN | Y | P | Y | P | Y | P | Y | P | P2 | LeadObserver + history models | Unify event and audit semantics |
| Import, API, webhook audit | YELLOW | P | P | Y | Y | P | P | P | Y | P2 | Capture logs + request ID fields | Connect external event audit consistently |
| Automation and merge audit | YELLOW | P | P | Y | P | P | P | P | P | P2 | Workflow/merge services | Add durable action/merge audit |
| Archive and restore audit | ORANGE | P | Y | P | P | P | Y | P | P | P1 | is_archived plus hard delete | Add restore and immutable archive history |
| Soft delete | RED | N | Y | P | P | N | Y | N | N | P1 | Original leads table has no soft-delete field | Implement domain-approved archival policy |
| Restore | RED | N | Y | P | P | N | Y | N | N | P1 | No verified deleted Lead restoration | Implement restore workflow |
| Permanent delete and dependency handling | YELLOW | Y | Y | Y | P | P | Y | P | P | P2 | LeadRepository delete and FK cascade | Define permanent-delete authorization and dependency rules |
| Contact/Person/Organization dependencies | GRAY | Y | Y | Y | P | P | P | P | Y | P2 | Legacy schema, Contact package, person fields | Refactor to Lead-owned identity fields before removal |
| Quote/Product dependencies inside Lead | GRAY | Y | Y | Y | P | P | P | P | P | P2 | LeadForm products validation and Quote controllers | Remove from Lead scope after dependency mapping |

## Overall matrix assessment

The audit request covers 191 individual capability checks when every listed subfeature is counted separately. The matrix groups related checks into reviewable clusters while retaining every requested capability.

Summary across the 191 requested capability checks:

| Status | Count |
|---|---:|
| Complete | 61 |
| Partial | 68 |
| Broken / Incorrect | 37 |
| Missing | 19 |
| UI only | 0 |
| Backend only | 0 |
| Unused / Out of scope | 6 |

The counts intentionally classify partially connected and bypassed functionality conservatively. A capability is not green because a UI element or service exists.

## Highest-risk matrix clusters

1. Lead bulk operations
2. Object-level permission enforcement
3. Webhook signature and replay security
4. SLA storage and execution
5. Lifecycle orchestration
6. Duplicate detection consistency
7. Follow-up state semantics
8. Automation idempotency
9. Archive/restore lifecycle
10. Contact/Quote/Product dependency removal
