# Lead Operations Audit Feature Matrix

Repository: `nvnkmr127/privyr-1`

Audited commit: `84f2347fde7a12d0f945aa76eb5f2adcc3c17c9b`

Audit branch: `audit/full-codebase-audit-2026-08-21`

The rows below group closely related requirements into 72 auditable capability clusters. A feature is GREEN only when the execution path, persistence, authorization, validation, error handling and relevant side effects are sufficiently connected. Presence of a route, model, migration or UI component is not treated as completion.

Legend:
- GREEN: complete enough for the stated Lead Operations scope
- YELLOW: implemented but incomplete, weakly tested, or missing an important connection
- ORANGE: implementation exists but paths are inconsistent or incorrect
- RED: missing or operationally broken
- GRAY: outside the approved Lead-only product boundary or obsolete transition residue

| # | Capability | Status | Evidence | Key gap or assessment | Priority |
|---:|---|---|---|---|---|
| 1 | Lead creation | ORANGE | `LeadController::store`, `LeadRepository::create`, `LeadIngestionService::ingest` | Manual and external creation use different business paths | P1 |
| 2 | Lead editing | YELLOW | `LeadController::update`, `LeadRepository::update` | Generic repository update also owns qualification, assignment and scoring | P2 |
| 3 | Lead viewing | GREEN | `LeadController::view`, `LeadPolicy::view` | Policy and visibility checks exist | P2 |
| 4 | Lead archive | YELLOW | `swipeAction`, `is_archived` | Archive is a flag, not a complete archive domain flow with history | P2 |
| 5 | Lead restore | YELLOW | Inbox unarchive action | Restore semantics and audit coverage need stronger guarantees | P2 |
| 6 | Lead delete | YELLOW | `LeadController::destroy` | Dependency and historical retention behavior needs final verification | P2 |
| 7 | Lead search | GREEN | Lead DataGrid, repository search fields | Functional search exists | P2 |
| 8 | Lead filters | GREEN | Kanban filters and Inbox filters | Functional filters exist | P2 |
| 9 | Lead sorting and pagination | GREEN | DataGrid and Inbox paginator | Runtime scale tests still needed | P2 |
| 10 | Lead Kanban | GREEN | `LeadController::get`, Lead views | Stage board exists with stage resources | P2 |
| 11 | Kanban stage movement | YELLOW | `updateStage`, `LeadObserver` | Several stage mutation paths exist | P1 |
| 12 | Lead bulk update | ORANGE | `massUpdate`, bulk services | Multiple mutation mechanisms create inconsistent side effects | P1 |
| 13 | Lead bulk delete | YELLOW | `massDestroy` | Authorization filter exists, but deletion side effects need unified domain handling | P2 |
| 14 | Lead bulk reassign | GREEN | `massReassign`, `LeadAssignmentService` | Uses authorized-record filtering and assignment service | P2 |
| 15 | Lead export | YELLOW | Lead DataGrid/DataTransfer | ACL and large-volume memory behavior require verification | P2 |
| 16 | CSV import | YELLOW | Import Wizard, `LeadIngestionBatch` | Queue batch is functional but large retry boundary is coarse | P2 |
| 17 | Import column mapping | GREEN | Import Wizard routes and DataTransfer | Mapping path exists | P2 |
| 18 | Import preview and validation | YELLOW | Import Wizard | Full runtime behavior not executed | P2 |
| 19 | Import duplicate detection | GREEN | `LeadIngestionBatch`, `LeadIngestionService` | Canonical ingestion path used | P2 |
| 20 | Import update-existing | YELLOW | duplicateAction `update` | Repository lifecycle restrictions also apply to imported updates | P1 |
| 21 | Import history | GREEN | DataTransfer import state and summaries | History exists at import level | P2 |
| 22 | Open lifecycle | GREEN | `LeadLifecycleService::STATUS_OPEN` | Supported | P2 |
| 23 | Working lifecycle | GREEN | `LeadLifecycleService::STATUS_WORKING` | Supported | P2 |
| 24 | Nurturing lifecycle | ORANGE | `LeadNurtureService`, `LeadRepository::update` | Status is stripped by repository update | P0 |
| 25 | Converted lifecycle | YELLOW | `LeadLifecycleService` | Orchestration exists, but cross-entry consistency needs tests | P1 |
| 26 | Lost lifecycle | YELLOW | `LeadLifecycleService` | Reason handling exists, cross-entry consistency needs tests | P1 |
| 27 | Junk lifecycle | YELLOW | `LeadLifecycleService` | Reason handling exists, cross-entry consistency needs tests | P1 |
| 28 | Reopen lifecycle | GREEN | `LeadLifecycleService::reopenLead` | Lost/Junk can reopen to Working | P2 |
| 29 | Initial lifecycle state | RED | `LeadRepository::create`, `LeadIngestionService` | Repository default `Active` is outside lifecycle constants | P0 |
| 30 | Qualification status | GREEN | Qualification controller, service, history | Status workflow exists | P2 |
| 31 | Qualification fields | YELLOW | Lead EAV and qualification model | Shared validation boundary needs consolidation | P2 |
| 32 | Qualification history | GREEN | Qualification repository and events | History is persisted | P2 |
| 33 | Qualification score | GREEN | `LeadScoringEngine` | Scoring engine is invoked on create/update | P2 |
| 34 | Qualification score history | YELLOW | LeadObserver score logging | Not a dedicated score history domain | P2 |
| 35 | Qualification validation | YELLOW | `LeadDataQualityService`, LeadForm | Manual and ingestion validation differ | P2 |
| 36 | Pipelines | GREEN | Pipeline repositories/controllers | CRUD is present | P2 |
| 37 | Stages | GREEN | Stage repositories/controllers | CRUD is present | P2 |
| 38 | Stage ordering | GREEN | stage sort order and Kanban | Ordering exists | P2 |
| 39 | Stage history | GREEN | `LeadObserver` | Stage history is created on model update | P2 |
| 40 | Stage aging | GREEN | `stage_changed_at`, Lead model accessors | Core calculation exists | P2 |
| 41 | Owner assignment | GREEN | `LeadAssignmentService` | Manual and automatic paths exist | P2 |
| 42 | Team assignment | GREEN | Assignment service groups | Team assignment exists | P2 |
| 43 | Automatic assignment | YELLOW | Assignment rules | Rule engine exists, operational coverage needs stronger tests | P1 |
| 44 | Round robin | YELLOW | `LeadAssignmentService::executeRule` | Concurrency logic exists but needs fairness tests | P1 |
| 45 | Weighted assignment | YELLOW | same | Implemented, runtime coverage unverified | P2 |
| 46 | Least-assigned | YELLOW | same | Implemented, count query needs scale review | P2 |
| 47 | Capacity-based assignment | YELLOW | same | Implemented, capacity semantics need verification | P2 |
| 48 | Assignment history | GREEN | `LeadAssignmentRepository` | Persisted | P2 |
| 49 | Assignment failures | YELLOW | assignment service boolean failures | No durable failure ledger | P1 |
| 50 | Assignment fallback | YELLOW | rule fallback support | No-rule outcome intentionally leaves Lead unassigned | P1 |
| 51 | Follow-up creation | GREEN | FollowUpController/services | Core follow-up operations exist | P2 |
| 52 | Follow-up completion | YELLOW | complete endpoints and scheduled command | Needs complete activity state and notification verification | P2 |
| 53 | Follow-up cancel | YELLOW | follow-up service | Cancellation semantics require final state review | P2 |
| 54 | Follow-up reschedule | GREEN | snooze/follow-up paths | Reschedule capability exists | P2 |
| 55 | Overdue follow-up | GREEN | Lead scopes and scheduled command | Overdue state exists | P2 |
| 56 | Next action | GREEN | Lead fields and Inbox preset | Operational visibility exists | P2 |
| 57 | Follow-up history | YELLOW | Activities and service history | Need first-class immutable history guarantees | P2 |
| 58 | Follow-up notifications | YELLOW | notifications/jobs | Coverage and delivery guarantees need runtime verification | P2 |
| 59 | Nurture reason | GREEN | nurture fields and reason migration | Stored and exposed | P2 |
| 60 | Nurture re-engagement date | YELLOW | nurture service and activity | Scheduling exists but status transition conflict remains | P1 |
| 61 | Nurture history | GREEN | nurture history model/service | History is persisted | P2 |
| 62 | Nurture automation | YELLOW | nurture events plus workflow system | Full execution chain needs integration tests | P1 |
| 63 | Notes and activities | GREEN | Activity package, Lead activities | Core logging is present | P2 |
| 64 | Calls and meetings | GREEN | Activity forms/services | Core scheduling path exists | P2 |
| 65 | Email communication | YELLOW | Email package and Lead email routes | Provider and message-id abstraction not uniform | P2 |
| 66 | Inbound communication | YELLOW | inbound email/IMAP infrastructure | Full Lead correlation and idempotency needs verification | P1 |
| 67 | Communication timeline | GREEN | Lead timeline and activities | Core timeline exists | P2 |
| 68 | External message IDs and idempotency | RED | No single channel-wide ledger found | Required for reliable replay-safe communication | P1 |
| 69 | Source attribution | GREEN | LeadAttributionService and Lead model | Source, origin and campaign fields exist | P2 |
| 70 | First/latest touch attribution | GREEN | Lead model and attribution history | Storage and history exist | P2 |
| 71 | Duplicate phone/email matching | YELLOW | `LeadDuplicateService`, `DuplicateMatchingService` | Two engines implement overlapping logic | P1 |
| 72 | Duplicate concurrency protection | RED | duplicate migration | Indexes exist, uniqueness guard is not established | P0 |
| 73 | Duplicate external ID matching | YELLOW | ingestion duplicate matching | Needs database uniqueness and event ledger | P1 |
| 74 | Duplicate warning UI | GREEN | LeadController duplicate warning | Warning path exists | P2 |
| 75 | Lead merge | YELLOW | `LeadMergeService` | Relationship reassignment exists, locking and service ACL need strengthening | P1 |
| 76 | Merge history | GREEN | `LeadMergeHistory` | History record exists | P2 |
| 77 | Automation triggers | GREEN | Automation Entity listener | Active workflows are selected by event | P2 |
| 78 | Automation conditions | GREEN | workflow validator | Condition evaluation exists | P2 |
| 79 | Automation actions | YELLOW | workflow action job and entity handlers | Full action matrix needs integration coverage | P1 |
| 80 | Scheduled automation | YELLOW | workflow jobs and Laravel queues | Scheduler connection needs explicit verification | P1 |
| 81 | Automation retry | GREEN | `ExecuteWorkflowActionJob::$tries = 3` | Retry exists | P2 |
| 82 | Automation idempotency | YELLOW | WorkflowExecution model/job | Key is workflow+entity+event, which needs a documented execution policy | P1 |
| 83 | Automation loop prevention | YELLOW | static execution depth guard | Cross-job chain protection is incomplete | P1 |
| 84 | Automation execution logs | GREEN | WorkflowExecution | Execution state and error are persisted | P2 |
| 85 | Assignment SLA | RED | SLA job/service exists but scheduler linkage is unverified | Operational execution is not proven | P1 |
| 86 | First action SLA | RED | same | Operational execution is not proven | P1 |
| 87 | Follow-up SLA | RED | same | Operational execution is not proven | P1 |
| 88 | Stage SLA | RED | same | Operational execution is not proven | P1 |
| 89 | Due-soon and breach | RED | `SlaEvaluatorService` | Evaluator exists but no verified trigger | P1 |
| 90 | SLA escalation | RED | no verified complete escalation path | Missing operational proof | P1 |
| 91 | SLA history | YELLOW | SLA entities/services exist | Durable lifecycle needs end-to-end verification | P1 |
| 92 | Phone normalization | GREEN | `LeadDataQualityService` | Deterministic normalization exists | P2 |
| 93 | Email normalization | GREEN | `LeadDataQualityService` | Deterministic normalization exists | P2 |
| 94 | Custom Lead attributes | GREEN | Attribute package and EAV repository | Dynamic fields exist | P2 |
| 95 | Required field validation | YELLOW | LeadForm and data quality | Multiple validation layers | P2 |
| 96 | Lead counts analytics | GREEN | `LeadAnalyticsService` | Core metrics exist | P2 |
| 97 | Source analytics | GREEN | source analytics service | Present | P2 |
| 98 | Campaign analytics | YELLOW | attribution data exists | Campaign reporting needs direct proof | P2 |
| 99 | Owner analytics | GREEN | owner analytics service | Present | P2 |
| 100 | Team analytics | YELLOW | owner/group infrastructure | Dedicated team metrics need verification | P2 |
| 101 | Pipeline analytics | GREEN | pipeline analytics service | Present | P2 |
| 102 | Stage aging analytics | YELLOW | model and trend components | Full reporting path needs verification | P2 |
| 103 | Nurture analytics | YELLOW | nurture tables and analytics candidates | Dedicated metrics not established as a single contract | P2 |
| 104 | Follow-up analytics | YELLOW | core metrics include overdue follow-ups | Broader follow-up performance metrics need completion | P2 |
| 105 | SLA analytics | RED | SLA execution unverified | No trustworthy operational metric source | P1 |
| 106 | Action Center | GREEN | Lead Inbox presets | Today/new/overdue/unassigned states exist | P2 |
| 107 | Import API | YELLOW | public CSV import route | Needs hardened auth and import lifecycle review | P1 |
| 108 | Lead ingestion API | YELLOW | `routes/api.php`, LeadCaptureController | Shared secret and replay protection need hardening | P1 |
| 109 | Webhook authentication | YELLOW | generic secret plus Facebook HMAC | Provider-specific security differs | P1 |
| 110 | Webhook replay protection | RED | no common delivery ledger verified | Required for reliable ingestion | P1 |
| 111 | Permission roles | GREEN | Bouncer and LeadPolicy | RBAC exists | P2 |
| 112 | Own/team/all Lead visibility | GREEN | LeadVisibilityService | Core scope exists | P2 |
| 113 | Field-level permissions | YELLOW | ACL focuses on actions | Complete field-level policy not demonstrated | P2 |
| 114 | API permissions | YELLOW | Sanctum and capture secrets | Connector-specific authorization policy needs strengthening | P1 |
| 115 | Automation permissions | YELLOW | workflow ACL and queue execution | Execution-time authorization contract needs proof | P2 |
| 116 | Import/export permissions | YELLOW | route middleware/DataGrid | End-to-end permission tests needed | P2 |
| 117 | Lead audit changes | GREEN | LeadObserver | Key Lead changes are logged | P2 |
| 118 | Assignment audit | GREEN | observer + assignment history | Both paths present | P2 |
| 119 | Lifecycle audit | GREEN | observer + status history service | Core lifecycle history exists | P2 |
| 120 | API/webhook audit | YELLOW | ingestion logs/events | Source-specific audit context needs consolidation | P2 |
| 121 | Automation audit | GREEN | WorkflowExecution | Execution record exists | P2 |
| 122 | Merge audit | YELLOW | LeadMergeHistory | Needs explicit ACL and immutable audit linkage | P1 |
| 123 | Archive/restore audit | YELLOW | model activity plus archive flag | Dedicated audit contract needs confirmation | P2 |
| 124 | Contact/Person/Organization removal | ORANGE | removal migrations plus remaining views/docs/fields | Transition is incomplete | P0 |
| 125 | Contact-specific documentation | GRAY | docs/features/contact-management.md and CRUD docs | Outside approved Lead-only scope | P3 |
| 126 | Campaign/Marketing modules | GRAY | Marketing package and docs | Outside approved Lead-only scope unless retained strictly as future integration adapters | P3 |
| 127 | Quote/Deal dependencies | YELLOW | Lead-oriented code search required | Must confirm no commercial entity dependency remains | P1 |
| 128 | Full runtime regression suite | YELLOW | Pest/PHPUnit dependencies and CI workflows | Runtime execution not performed through connector | P1 |
| 129 | Browser regression suite | YELLOW | Playwright workflow exists | Runtime execution not performed through connector | P1 |
| 130 | Static analysis and lint | YELLOW | Laravel Pint in composer | Runtime execution not performed through connector | P2 |
| 131 | Large-data performance | YELLOW | Inbox/Kanban repository queries | Requires runtime load tests | P2 |
| 132 | Import performance | YELLOW | `LeadIngestionBatch` | One queued job processes many rows | P2 |
| 133 | Dead code / orphaned code | YELLOW | repository searches | Multiple overlapping paths need a dependency cleanup pass | P2 |
| 134 | Documentation accuracy | ORANGE | supplied docs and repo docs | Current docs still describe removed Contact concepts | P2 |

## Summary counts

The matrix contains 134 capability rows after expanding the requested Lead Operations scope into individual audit points.

The original 72 grouped clusters were used during the first review pass. The expanded matrix above preserves those findings and adds the detailed sub-capabilities required for final product readiness.

The status distribution is intentionally not collapsed into a single completion percentage because production readiness depends on the P0 and P1 paths, not on feature count alone.

## Highest-impact rows

1. Initial lifecycle state: RED, P0.
2. Nurturing lifecycle: ORANGE, P0.
3. Duplicate concurrency protection: RED, P0.
4. Contact/Person/Organization removal: ORANGE, P0.
5. Public ingestion API security: YELLOW, P1.
6. SLA execution: RED, P1.
7. Duplicate engine consolidation: YELLOW, P1.
8. Manual versus canonical ingestion consistency: ORANGE, P1.
9. Webhook replay protection: RED, P1.
10. Quote/Deal dependency removal verification: YELLOW, P1.
