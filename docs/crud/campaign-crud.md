# Campaign CRUD Operations

This document details the Create, Read, Update, and Delete operations for the Campaign entity in Krayin CRM.

## Entity: Campaign

### Create
- **UI Entry Point**: Top-right "Create Campaign" button in `/admin/campaigns`.
- **Form**: Drawer/Modal.
- **Required Fields**: 
  - `name`
  - `subject`
  - `email_template_id`
  - `sender_name`
  - `sender_email`
- **Frontend Validation**: Vue.js validation.
- **API Endpoint**: `POST /admin/campaigns/create`
- **Backend Logic**:
  1. Validates request using `CampaignFormRequest`.
  2. Dispatches `marketing.campaigns.create.before`.
  3. `CampaignRepository->create()`.
  4. Dispatches `marketing.campaigns.create.after`.
- **Database Operation**: `INSERT INTO campaigns...`

### Read

#### List (DataGrid)
- **API**: `GET /admin/campaigns/get`
- **Search**: Global search by `name`, `subject`.
- **Sorting**: Supported on ID, Name.
- **Pagination**: 10/20/50 per page.

#### Detail
- **API**: `GET /admin/campaigns/edit/{id}`
- **UI**: The edit form serves as the detail view.

### Update
- **UI Entry Point**: Datagrid row actions (Edit icon).
- **Editable Fields**: All fields.
- **API Endpoint**: `PUT /admin/campaigns/edit/{id}`
- **Backend Behavior**:
  1. Validation.
  2. `marketing.campaigns.update.before`.
  3. `CampaignRepository->update()`.
  4. `marketing.campaigns.update.after`.

### Delete
- **Delete Trigger**: Datagrid row action (Trash icon).
- **Confirmation Dialog**: Required.
- **API Endpoint**: `DELETE /admin/campaigns/{id}`
- **Soft Delete vs Hard Delete**: Hard delete.
- **Permission Checks**: Checked via ACL.
