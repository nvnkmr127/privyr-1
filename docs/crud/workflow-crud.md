# Workflow CRUD Operations

This document details the Create, Read, Update, and Delete operations for the Workflow entity in Krayin CRM.

## Entity: Workflow

### Create
- **UI Entry Point**: Top-right "Create Workflow" button in `/admin/settings/workflows`.
- **Form**: Full page view (due to complexity).
- **Required Fields**: 
  - `name` (String)
  - `event` (String)
  - `actions` (Array of objects)
- **Frontend Validation**: Vue.js validation.
- **API Endpoint**: `POST /admin/settings/workflows/create`
- **Request Payload**: JSON/Form Data. The conditions and actions are typically serialized as arrays of objects.
- **Backend Logic**:
  1. Validates request using `WorkflowFormRequest`.
  2. Dispatches `settings.automation.workflows.create.before`.
  3. `WorkflowRepository->create()` handles saving the main workflow, parsing the conditions into a standardized JSON format, and creating child records for `actions`.
  4. Dispatches `settings.automation.workflows.create.after`.
- **Database Operation**: `INSERT INTO workflows`, followed by `INSERT INTO workflow_actions`.

### Read

#### List (DataGrid)
- **API**: `GET /admin/settings/workflows/get`
- **Search**: Global search by `name`.
- **Sorting**: Supported on ID, Name.
- **Pagination**: 10/20/50 per page.

#### Detail
- **API**: `GET /admin/settings/workflows/edit/{id}`
- **UI**: The edit form serves as the detail view.

### Update
- **UI Entry Point**: Datagrid row actions (Edit icon).
- **Editable Fields**: All (Name, Description, Events, Conditions, Actions).
- **Validation**: Same as Create.
- **API Endpoint**: `PUT /admin/settings/workflows/edit/{id}`
- **Backend Behavior**:
  1. Validation.
  2. `settings.automation.workflows.update.before`.
  3. `WorkflowRepository->update()` updates the main record. Re-syncs or deletes/recreates `workflow_actions`.
  4. `settings.automation.workflows.update.after`.

### Delete
- **Delete Trigger**: Datagrid row action (Trash icon).
- **Confirmation Dialog**: Required.
- **API Endpoint**: `DELETE /admin/settings/workflows/{id}`
- **Soft Delete vs Hard Delete**: Hard delete.
- **Related Records**: Cascading deletes for `workflow_actions`.
- **Permission Checks**: Checked via ACL.
