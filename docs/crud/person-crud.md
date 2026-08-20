# Person CRUD Operations

This document details the Create, Read, Update, and Delete operations for the Person entity (under Contact Management) in Krayin CRM.

## Entity: Person

### Create
- **UI Entry Point**: Top-right "Create Person" button in `/admin/contacts/persons`.
- **Form**: Drawer/Modal or separate view.
- **Required Fields**: 
  - `name` (String)
  - `emails` (Array/Repeater of valid emails)
- **Frontend Validation**: Vue.js validation for required fields and valid email format.
- **API Endpoint**: `POST /admin/contacts/persons/create`
- **Request Payload**: JSON or Form Data containing name, emails, contact_numbers, organization_id, and any custom EAV attributes.
- **Backend Logic**:
  1. Validates request using `PersonFormRequest`.
  2. Dispatches `contact.person.create.before` event.
  3. `PersonRepository->create()` handles saving to `persons` table.
  4. Saves EAV attributes to `person_attribute_values` table.
  5. Dispatches `contact.person.create.after` event.
- **Database Operation**: `INSERT INTO persons...`
- **Success Behavior**: Flash success message. Redirects to persons list or updates Datagrid.

### Read

#### List (DataGrid)
- **API**: `GET /admin/contacts/persons` (Returns JSON for Datagrid).
- **Search**: Global search by `name`, `emails`, `contact_numbers`.
- **Filters**: By Organization, creation date.
- **Sorting**: Supported on ID, Name.
- **Pagination**: 10/20/50 per page.

#### Detail
- **API**: `GET /admin/contacts/persons/view/{id}`
- **UI**: A comprehensive view showing person data, custom attributes, related Organization, associated Leads, and Activity Timeline (Notes, Emails, Calls, Meetings).

### Update
- **UI Entry Point**: Datagrid row actions (Edit icon) or Edit button inside the Person Detail View.
- **Editable Fields**: All fields (Name, Emails, Phones, Organization, custom attributes).
- **Validation**: Same as Create.
- **API Endpoint**: `PUT /admin/contacts/persons/edit/{id}`
- **Backend Behavior**:
  1. Validation.
  2. `contact.person.update.before` event.
  3. `PersonRepository->update()`.
  4. `contact.person.update.after` event.
- **Database Update**: `UPDATE persons SET ... WHERE id = ?`
- **UI Refresh**: Flash message, state updated, redirect back to index or detail view.

### Delete
- **Delete Trigger**: Datagrid row action (Trash icon) or Edit view action.
- **Confirmation Dialog**: Required before deletion.
- **API Endpoint**: `DELETE /admin/contacts/persons/{id}`
- **Soft Delete vs Hard Delete**: Hard delete.
- **Related Records**: Cascading deletes for activities, tags, attributes. Nullifies `person_id` on Leads depending on constraints.
- **Permission Checks**: Checked via Bouncer ACL `can:contacts.persons.delete`.

### Other Operations

#### Mass Actions
- **Bulk Delete**: Select multiple rows in DataGrid -> Mass Action -> Delete.
