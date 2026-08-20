# Automation & Workflows

## 1. Feature Name
Automation (Workflows)

## 2. What Is This Feature?
Automations in Krayin CRM allow administrators to set up rule-based workflows that execute specific actions when a specific event occurs and certain conditions are met. This removes manual data entry and repetitive tasks.

## 3. How Is It Useful?
It allows businesses to automate their sales processes. For example:
- Send an automatic welcome email when a Lead is created.
- Update a Lead's status automatically if a specific tag is applied.
- Assign a Lead to a specific sales rep based on the Lead Source.

## 4. Users / Roles
- **Admin**: Full access. Only administrators typically have access to create and modify system-wide automations.
- **User**: No access to view or configure workflows, though their actions may trigger them.

## 5. Frontend / UI

### Page / Screen
- **Route**: `/admin/settings/workflows`
- **Page Purpose**: View and manage all automated workflows.
- **Navigation Location**: Settings (Gear Icon) -> Automation -> Workflows.

### UI Components
- **DataGrid**: Tabular list of configured workflows.
- **Form View**: A complex form with three main sections:
  1. **Basic Info**: Name, Description.
  2. **Event & Conditions**: What triggers the workflow (e.g., `lead.create`) and under what conditions (e.g., `lead_value > 1000`).
  3. **Actions**: What happens when triggered (e.g., Send Email, Update Field).

### Available User Options
```text
Actions
├── View/Edit Workflow
├── Create Workflow
├── Delete Workflow
└── Mass Delete
```

### Form Fields (Create/Edit)
| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| Name | Text | Yes | Name of the workflow. |
| Description | Textarea | No | Purpose of the workflow. |
| Event | Dropdown | Yes | The system event that triggers this (e.g., Lead Created, Lead Updated). |
| Condition(s) | Dynamic Repeater | No | Rules that must evaluate to true (e.g., Field X Equals Y). |
| Action(s) | Dynamic Repeater | Yes | Actions to perform (e.g., Send Email, Update Attribute). |

## 6. User Workflow
```text
Admin navigates to Settings -> Workflows
    ↓
Clicks "Create Workflow"
    ↓
Selects "Lead Created" as the Event
    ↓
Adds Condition: "Lead Source" equals "Website"
    ↓
Adds Action: "Send Email" to Owner
    ↓
Saves Workflow
    ↓
Backend saves to `workflows` and `workflow_actions` tables
    ↓
Later, when a user creates a lead from the Website, the event dispatcher triggers this workflow and sends the email automatically.
```

## 7. CRUD Operations
*See [docs/crud/workflow-crud.md](../crud/workflow-crud.md).*

## 8. API Documentation
```text
Method: GET
Endpoint: /admin/settings/workflows
Purpose: Load Workflows DataGrid.

Method: POST
Endpoint: /admin/settings/workflows/create
Purpose: Create a new workflow.

Method: PUT
Endpoint: /admin/settings/workflows/edit/{id}
Purpose: Update existing workflow.

Method: DELETE
Endpoint: /admin/settings/workflows/{id}
Purpose: Delete a workflow.
```

## 9. Database / Data Model
**Tables**: `workflows`, `workflow_actions`

- `workflows`: `id`, `name`, `description`, `event`, `condition` (JSON/serialized), `status`.
- `workflow_actions`: `id`, `workflow_id`, `action_type`, `value` (JSON/serialized).

## 10. Relationships
```text
Workflow
 └── has many → WorkflowActions
```

## 11. Data Flow
```text
UI (Settings Form)
 ↓
WorkflowController
 ↓
WorkflowRepository (Saves to DB)
 ↓
...
System Event Dispatched (e.g., `Event::dispatch('lead.create.after', $lead)`)
 ↓
Workflow Event Listener (listens to all registered workflow events)
 ↓
Evaluates conditions against the triggering Entity (Lead)
 ↓
Executes defined WorkflowActions
```

## 12. Business Rules
- Workflows must have at least one action defined.
- Conditions are optional; if blank, the workflow fires on the event unconditionally.

## 13. Permissions and Authorization
- `settings.automation.workflows.view`, `create`, `edit`, `delete`.

## 14. Files / Code Locations
- Frontend Views: `packages/Webkul/Admin/src/Resources/views/settings/workflows/`
- Controller: `WorkflowController.php`
- Repository: `WorkflowRepository.php`
- Model: `Workflow.php`
- Core Logic: Usually handled by an Event Listener/Subscriber within the Automation package.
