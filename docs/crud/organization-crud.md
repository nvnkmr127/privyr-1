# Organization CRUD Operations

This document details the Create, Read, Update, and Delete operations for the Organization entity (under Contact Management) in Krayin CRM.

## Entity: Organization

### Create
- **UI Entry Point**: Top-right "Create Organization" button in `/admin/contacts/organizations`.
- **Form**: Drawer/Modal.
- **Required Fields**: 
  - `name` (String)
- **Frontend Validation**: Vue.js validation for required name.
- **API Endpoint**: `POST /admin/contacts/organizations/create`
- **Request Payload**: JSON or Form Data containing name, address, and custom attributes.
- **Backend Logic**:
  1. Validates request using `OrganizationFormRequest`.
  2. Dispatches `contact.organization.create.before` event.
  3. `OrganizationRepository->create()` saves to `organizations` table.
  4. Saves EAV attributes.
  5. Dispatches `contact.organization.create.after` event.
- **Database Operation**: `INSERT INTO organizations...`
- **Success Behavior**: Flash success message and Datagrid refresh.

### Read

#### List (DataGrid)
- **API**: `GET /admin/contacts/organizations` (Returns JSON for Datagrid).
- **Search**: Global search by `name`.
- **Filters**: By Name, creation date.
- **Sorting**: Supported on ID, Name.
- **Pagination**: 10/20/50 per page.

#### Detail
- **API**: `GET /admin/contacts/organizations/view/{id}`
- **UI**: View showing organization data, associated Persons, associated Leads, and Activity Timeline.

### Update
- **UI Entry Point**: Datagrid row actions (Edit icon) or Detail view.
- **Editable Fields**: Name, Address, Custom Attributes.
- **Validation**: Same as Create.
- **API Endpoint**: `PUT /admin/contacts/organizations/edit/{id}`
- **Backend Behavior**:
  1. Validation.
  2. `contact.organization.update.before`.
  3. `OrganizationRepository->update()`.
  4. `contact.organization.update.after`.
- **Database Update**: `UPDATE organizations SET ... WHERE id = ?`

### Delete
- **Delete Trigger**: Datagrid row action (Trash icon).
- **Confirmation Dialog**: Required before deletion.
- **API Endpoint**: `DELETE /admin/contacts/organizations/{id}`
- **Soft Delete vs Hard Delete**: Hard delete.
- **Related Records**: Cascading deletes for activities, tags, attributes. Persons belonging to this organization will have their `organization_id` set to `null` (ON DELETE SET NULL).
- **Permission Checks**: Checked via Bouncer ACL `can:contacts.organizations.delete`.

### Other Operations

#### Mass Actions
- **Bulk Delete**: Select multiple rows in DataGrid -> Mass Action -> Delete.
