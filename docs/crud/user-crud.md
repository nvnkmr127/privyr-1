# User CRUD Operations

This document details the Create, Read, Update, and Delete operations for the User entity in Krayin CRM.

## Entity: User

### Create
- **UI Entry Point**: Top-right "Create User" button in `/admin/settings/users`.
- **Form**: Drawer/Modal.
- **Required Fields**: 
  - `name`
  - `email` (Must be unique)
  - `password`
  - `role_id`
- **Frontend Validation**: Vue.js validation.
- **API Endpoint**: `POST /admin/settings/users/create`
- **Backend Logic**:
  1. Validates request using `UserFormRequest`.
  2. Hashes the password (`bcrypt`).
  3. `UserRepository->create()`.
  4. Syncs `user_groups`.
- **Database Operation**: `INSERT INTO users...`

### Read

#### List (DataGrid)
- **API**: `GET /admin/settings/users/get`
- **Search**: Global search by `name`, `email`.
- **Sorting**: Supported on ID, Name.
- **Pagination**: 10/20/50 per page.

#### Detail
- **API**: `GET /admin/settings/users/edit/{id}`
- **UI**: The edit form serves as the detail view.

### Update
- **UI Entry Point**: Datagrid row actions (Edit icon).
- **Editable Fields**: All fields (Password is optional; if left blank, it is not updated).
- **API Endpoint**: `PUT /admin/settings/users/edit/{id}`
- **Backend Behavior**:
  1. Validation (Email uniqueness ignores current user ID).
  2. `UserRepository->update()`.

### Delete
- **Delete Trigger**: Datagrid row action (Trash icon).
- **Confirmation Dialog**: Required.
- **API Endpoint**: `DELETE /admin/settings/users/{id}`
- **Soft Delete vs Hard Delete**: Hard delete.
- **Business Rule**: Cannot delete the currently logged-in user or the primary super-admin.
- **Permission Checks**: Checked via ACL `can:settings.users.users.delete`.
