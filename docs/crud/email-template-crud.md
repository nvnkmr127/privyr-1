# Email Template CRUD Operations

This document details the Create, Read, Update, and Delete operations for the Email Template entity in Krayin CRM.

## Entity: Email Template

### Create
- **UI Entry Point**: Top-right "Create Email Template" button in `/admin/settings/email-templates`.
- **Form**: Full page form with Rich Text Editor.
- **Required Fields**: 
  - `name`
  - `subject`
  - `content`
- **Frontend Validation**: Vue.js validation.
- **API Endpoint**: `POST /admin/settings/email-templates/create`
- **Backend Logic**:
  1. Validates request using `EmailTemplateFormRequest`.
  2. Dispatches `settings.email_templates.create.before`.
  3. `EmailTemplateRepository->create()`.
  4. Dispatches `settings.email_templates.create.after`.
- **Database Operation**: `INSERT INTO email_templates...`

### Read

#### List (DataGrid)
- **API**: `GET /admin/settings/email-templates/get`
- **Search**: Global search by `name`, `subject`.
- **Sorting**: Supported on ID, Name.
- **Pagination**: 10/20/50 per page.

#### Detail
- **API**: `GET /admin/settings/email-templates/edit/{id}`
- **UI**: The edit form serves as the detail view.

### Update
- **UI Entry Point**: Datagrid row actions (Edit icon).
- **Editable Fields**: All fields.
- **API Endpoint**: `PUT /admin/settings/email-templates/edit/{id}`
- **Backend Behavior**:
  1. Validation.
  2. `settings.email_templates.update.before`.
  3. `EmailTemplateRepository->update()`.
  4. `settings.email_templates.update.after`.

### Delete
- **Delete Trigger**: Datagrid row action (Trash icon).
- **Confirmation Dialog**: Required.
- **API Endpoint**: `DELETE /admin/settings/email-templates/{id}`
- **Soft Delete vs Hard Delete**: Hard delete.
- **Permission Checks**: Checked via ACL `can:settings.email_templates.delete`.
