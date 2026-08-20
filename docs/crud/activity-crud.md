# Activity CRUD Operations

This document details the Create, Read, Update, and Delete operations for the Activity entity in Krayin CRM.

## Entity: Activity

### Create
- **UI Entry Point**: 
  - Global: Top-right "Create Activity" button in `/admin/activities`.
  - Contextual: Activity tabs (Note/Call/Meeting/Email) inside Lead/Person/Organization Detail view.
- **Form**: Drawer/Modal or inline tab form.
- **Required Fields**: 
  - `type` (Note, Call, Meeting, Email)
  - `title`
  - `schedule_from` / `schedule_to` (for Calls/Meetings)
- **Frontend Validation**: Vue.js form validation based on `type`.
- **API Endpoint**: `POST /admin/activities/create`
- **Request Payload**: JSON containing fields above, plus `lead_id` or `person_id` if contextual.
- **Backend Logic**:
  1. Validates request (`ActivityFormRequest`).
  2. Dispatches `activity.create.before` event.
  3. `ActivityRepository->create()` saves to `activities` table.
  4. Saves participants to `activity_participants` if applicable.
  5. Dispatches `activity.create.after` event.
- **Database Operation**: `INSERT INTO activities...`

### Read

#### List (DataGrid)
- **API**: `GET /admin/activities/get` (Returns JSON).
- **Search**: Global search by `title`.
- **Filters**: By Type, Status (Planned/Done).
- **Sorting**: ID, Title, Schedule Dates.
- **Pagination**: 10/20/50 per page.

#### Detail (Timeline View)
- **API**: Often fetched alongside the parent model (Lead/Person) or via a dedicated endpoint like `GET /admin/activities/file/{id}` for attachments.
- **UI**: Displayed as chronological cards in the UI timeline.

### Update
- **UI Entry Point**: Edit icon on Activity Datagrid or Timeline card.
- **Editable Fields**: Title, Comment, Schedule Times, Participants.
- **API Endpoint**: `PUT /admin/activities/edit/{id}`
- **Backend Behavior**:
  1. Validation.
  2. `activity.update.before`.
  3. `ActivityRepository->update()`.
  4. Syncs `activity_participants`.
  5. `activity.update.after`.
- **Database Update**: `UPDATE activities SET ... WHERE id = ?`

### Delete
- **Delete Trigger**: Trash icon on Activity Datagrid or Timeline card.
- **Confirmation Dialog**: Required.
- **API Endpoint**: `DELETE /admin/activities/{id}`
- **Soft Delete vs Hard Delete**: Hard delete.
- **Related Records**: Cascading deletes for `activity_participants`.
- **Permission Checks**: Checked via Bouncer ACL `can:activities.delete`.

### Other Operations

#### Mark as Done
- **UI**: Checkbox or status toggle on a scheduled Call/Meeting card.
- **API Endpoint**: `PUT /admin/activities/update-status/{id}`
- **Payload**: `{'is_done': 1}`
- **Database**: Updates `is_done` flag in `activities` table.
