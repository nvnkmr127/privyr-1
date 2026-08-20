# Lead CRUD Operations

This document details the Create, Read, Update, and Delete operations for the Lead entity in Krayin CRM.

## Entity: Lead

### Create
- **UI Entry Point**: Top-right "Create Lead" button in `/admin/leads`.
- **Form**: Drawer/Modal overlay.
- **Required Fields**: 
  - `title` (String)
  - `person_id` (Contact ID)
  - `lead_pipeline_id`
  - `lead_pipeline_stage_id`
- **Frontend Validation**: Vue.js VeeValidate ensures required fields are filled before enabling submission.
- **API Endpoint**: `POST /admin/leads/create`
- **Request Payload**: JSON or Form Data containing the fields above.
- **Backend Logic**:
  1. Validates request using `LeadFormRequest`.
  2. Dispatches `lead.create.before` event.
  3. `LeadRepository->create()` handles saving to `leads` table and handles any EAV attributes.
  4. Dispatches `lead.create.after` event.
- **Database Operation**: `INSERT INTO leads...`
- **Success Behavior**: Flash success message. Kanban/DataGrid is re-fetched to display the new lead.

### Read

#### List (DataGrid & Kanban)
- **API**: `GET /admin/leads/get` (Returns JSON for Datagrid/Kanban).
- **Search**: Global search by `title`, `person name`.
- **Filters**: State, Owner, Dates.
- **Sorting**: Supported on Datagrid for specific columns (ID, Title, Value).
- **Pagination**: Datagrid natively supports 10/20/50 per page. Kanban fetches leads per stage.

#### Detail
- **API**: `GET /admin/leads/view/{id}`
- **UI**: A comprehensive detail view showing lead data, custom attributes, related contact info, and the Activity Timeline.

### Update
- **UI Entry Point**: Datagrid row actions (Edit icon), Kanban card click, or Edit button inside the Lead Detail View.
- **Editable Fields**: All fields (Title, Value, Stage, Owner, Expected Close Date, custom attributes).
- **Validation**: Similar to Create (minus some static fields).
- **API Endpoint**: `PUT /admin/leads/edit/{id}`
- **Backend Behavior**:
  1. Validation.
  2. `lead.update.before` event.
  3. `LeadRepository->update()`.
  4. `lead.update.after` event.
- **Database Update**: `UPDATE leads SET ... WHERE id = ?`
- **UI Refresh**: Flash message, state updated in Vuex/Local state, redirect back to index or detail view.

### Delete
- **Delete Trigger**: Datagrid row action (Trash icon) or Edit view action.
- **Confirmation Dialog**: Browser native `confirm()` or custom SweetAlert/Vue modal.
- **API Endpoint**: `DELETE /admin/leads/{id}`
- **Soft Delete vs Hard Delete**: Hard delete (removes row entirely).
- **Related Records**: Cascading deletes for activities, tags, attributes.
- **Permission Checks**: Checked via Bouncer ACL middleware `can:leads.delete`.

### Other Operations

#### Move Stage (Kanban)
- **UI**: Dragging a card between Kanban columns.
- **API Endpoint**: `PUT /admin/leads/update-stage/{id}`
- **Payload**: `{'lead_pipeline_stage_id': new_stage_id}`
- **Logic**: Updates the stage without requiring the full edit form.

#### Mass Actions
- **Bulk Delete**: Select multiple rows in DataGrid -> Mass Action -> Delete.
- **Bulk Update (Stage)**: Select multiple rows -> Mass Action -> Update Stage.
