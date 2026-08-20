# Lead Management

## 1. Feature Name
Lead Management

## 2. What Is This Feature?
Lead Management is the core feature of the CRM. It allows users to track potential sales or deals from initial contact through to closing (won or lost). It provides a structured way to manage the sales pipeline, track expected revenue, and log interactions related to a specific deal.

## 3. How Is It Useful?
Sales teams use Lead Management to:
- Visualize their sales pipeline (Kanban view).
- Ensure no potential deal falls through the cracks.
- Track expected value and closing dates for forecasting.
- Centralize all communication and activities related to a deal.

## 4. Users / Roles
- **Admin**: Full access (View, Create, Edit, Delete, Assign).
- **Sales Rep / User**: Can typically View, Create, and Edit leads assigned to them. Deletion permissions depend on specific role configurations.

## 5. Frontend / UI

### Page / Screen
- **Route**: `/admin/leads`
- **Page Purpose**: View and manage all leads.
- **Layout**: Features a toggleable view between a Kanban Board (default) and a DataGrid Table.
- **Navigation Location**: Main Sidebar -> Leads.

### UI Components
- **Kanban Board**: Columns representing pipeline stages. Drag-and-drop functionality to move leads between stages.
- **DataGrid**: A tabular view for bulk actions and advanced filtering.
- **Filter Sidebar**: Options to filter by owner, status, created date, etc.
- **Lead Detail View**: A comprehensive page showing lead details, related contact/organization, attributes, and an activity timeline (Notes, Emails, Calls, Meetings).

### Available User Options
```text
Actions
├── View Lead (Clicking on card/row)
├── Create Lead (Top right button)
├── Edit Lead (From detail view or DataGrid actions)
├── Delete Lead (From detail view or DataGrid actions)
└── Move Stage (Drag and drop in Kanban)
```

### Form Fields (Create/Edit)
| Field | Type | Required | Validation | Default | Description |
| ----- | ---- | -------- | ---------- | ------- | ----------- |
| Title | Text | Yes | String, Max 255 | None | Name of the deal/lead. |
| Lead Value | Numeric | No | Numeric | 0.00 | Expected revenue from the lead. |
| Expected Close Date | Date | No | Date | None | When the deal is expected to close. |
| Person/Contact | Lookup | Yes | Exists in persons | None | The primary contact for the lead. |
| Owner | Lookup | No | Exists in users | Current User | The sales rep assigned to the lead. |
| Pipeline | Lookup | Yes | Exists in lead_pipelines | Default Pipeline | The sales process pipeline to use. |
| Stage | Lookup | Yes | Exists in lead_pipeline_stages | First Stage | Current stage in the pipeline. |
| Tags | Multi-select | No | - | None | Categorization tags. |

### Table/List (DataGrid View)
- **Columns**: ID, Title, Person, Expected Close Date, Lead Value, Status/Stage.
- **Sortable**: ID, Title, Expected Close Date, Lead Value.
- **Search behavior**: Global search applies to Title and Person name.
- **Filters**: By Stage, By Owner, By Tag.
- **Pagination**: Standard Datagrid pagination (10/20/50 per page).

## 6. User Workflow
```text
User navigates to Leads Kanban
    ↓
Clicks "Create Lead"
    ↓
Form modal/drawer opens
    ↓
User enters Title, Lead Value, and selects a Person
    ↓
User submits form
    ↓
Frontend validates required fields
    ↓
POST request to `/admin/leads` API/Controller
    ↓
Backend validates request
    ↓
LeadRepository creates record in `leads` table
    ↓
Success response returned
    ↓
Kanban board refreshes to show new lead in the first stage
```

## 7. CRUD Operations
*See [docs/crud/lead-crud.md](../crud/lead-crud.md) for detailed CRUD documentation.*

## 8. API Documentation
*Note: Krayin CRM primarily uses web routes and controllers returning views or JSON for DataGrids/Kanban.*

```text
Method: GET
Endpoint: /admin/leads
Purpose: Load Kanban/DataGrid view.

Method: GET
Endpoint: /admin/leads/get
Purpose: Fetch leads data for DataGrid/Kanban (JSON).

Method: POST
Endpoint: /admin/leads/create
Purpose: Create a new lead.
Request Body: title, lead_value, person_id, expected_close_date, etc.

Method: PUT
Endpoint: /admin/leads/edit/{id}
Purpose: Update existing lead.

Method: DELETE
Endpoint: /admin/leads/{id}
Purpose: Delete a lead.
```

## 9. Database / Data Model
**Table Name**: `leads`

- `id`: Primary key
- `title`: String
- `description`: Text
- `lead_value`: Decimal
- `status`: Boolean (active/won/lost)
- `expected_close_date`: Date
- `closed_at`: Timestamp
- `user_id`: Foreign key (Owner)
- `person_id`: Foreign key
- `lead_pipeline_id`: Foreign key
- `lead_pipeline_stage_id`: Foreign key
- `lead_source_id`: Foreign key
- `lead_type_id`: Foreign key

## 10. Relationships
```text
Lead
 ├── belongs to → User (Owner)
 ├── belongs to → Person
 ├── belongs to → LeadPipeline
 ├── belongs to → LeadPipelineStage
 ├── has many → Activities (Emails, Notes, Calls, Meetings)
 └── belongs to many → Tags (via lead_tags)
```

## 11. Data Flow
```text
UI (Vue.js Kanban / Blade Form)
 ↓
Validation (Form Requests)
 ↓
LeadController
 ↓
LeadRepository (Handles DB abstractions)
 ↓
Database (`leads` table)
 ↓
Event Dispatch (e.g., `lead.create.after`)
 ↓
Response (Redirect or JSON)
 ↓
UI Update
```

## 12. Business Rules
- A lead must have a title.
- A lead must be associated with a Person.
- When a lead's stage is changed to a "Won" or "Lost" stage, the lead is considered closed.
- Activities related to a lead are tracked on its timeline.

## 13. Validation Rules
- `title`: required, string.
- `person_id`: required, exists in `persons` table.
- `lead_pipeline_id`: required.
- `lead_pipeline_stage_id`: required.
- `lead_value`: numeric, greater than or equal to 0.

## 14. Statuses and State Transitions
Stages are dynamic based on the Pipeline configuration, but generally follow:
```text
New / Prospect
  ↓
In Progress / Qualified
  ↓
Negotiation
  ↓
Won OR Lost
```

## 15. Permissions and Authorization
- Based on Bouncer/ACL configuration in `User` package.
- `leads.view`: Can view leads.
- `leads.create`: Can create leads.
- `leads.edit`: Can edit leads.
- `leads.delete`: Can delete leads.

## 16. Notifications
- Flash messages on success/error of CRUD operations.
- Potential email notifications if Automations/Workflows are configured on `lead.create.after`.

## 17. Error Handling
- Form validation errors are displayed inline beneath form fields.
- Not-found errors (404) if a user tries to access a deleted lead.
- Permission errors (403) if a user lacks ACL rights.

## 18. Edge Cases
- Deleting a Pipeline or Stage that has associated leads (handled via DB constraints or cascading).
- High volume of leads affecting Kanban performance (Kanban fetches limited records or handles infinite scrolling/lazy loading).

## 19. Dependencies
- **Contacts**: Requires Persons/Organizations to exist.
- **Users**: Requires Staff to assign ownership.
- **Attributes**: EAV system heavily drives Lead fields.

## 20. Files / Code Locations
- Frontend Views: `packages/Webkul/Admin/src/Resources/views/leads/`
- Controller: `packages/Webkul/Lead/src/Http/Controllers/LeadController.php`
- Repository: `packages/Webkul/Lead/src/Repositories/LeadRepository.php`
- Model: `packages/Webkul/Lead/src/Models/Lead.php`
