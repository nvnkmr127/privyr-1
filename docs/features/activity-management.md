# Activity Management

## 1. Feature Name
Activity Management

## 2. What Is This Feature?
Activity Management is a core operational feature that tracks all interactions and scheduled tasks associated with Leads, Persons, or Organizations. It logs emails, notes, phone calls, and meetings to keep a unified timeline of interactions.

## 3. How Is It Useful?
It provides users (sales/support staff) with a centralized history of communication. Users can see what has happened (Notes/Emails) and what is scheduled to happen (Calls/Meetings) without leaving the CRM. It ensures context is never lost when dealing with a client.

## 4. Users / Roles
- **Admin**: Full access to all activities across all users.
- **User**: Can view and manage activities assigned to them or related to records they own. Can create activities for their own leads.

## 5. Frontend / UI

### Page / Screen
- **Routes**: `/admin/activities`
- **Page Purpose**: List and manage all scheduled and past activities across the CRM.
- **Navigation Location**: Main Sidebar -> Activities.

### UI Components
- **DataGrid**: Tabular list of activities (Calls, Meetings, Tasks).
- **Filter Sidebar**: By Activity Type, Due Date, Status (Planned/Done).
- **Timeline/History Component**: This is embedded inside the detail views of Leads, Persons, and Organizations. It lists activities in chronological order.

### Available User Options
```text
Actions
├── View Activity
├── Create Activity (Note, Email, Call, Meeting, Task)
├── Edit Activity
├── Mark as Done (for scheduled calls/meetings)
└── Delete Activity
```

### Form Fields (Create Activity)
| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| Type | Dropdown | Yes | Note, Call, Meeting, Email. |
| Title | Text | Yes | Short summary or subject. |
| Comment/Body | Rich Text | No | The detailed content of the note/email. |
| Schedule From/To | Datetime | Conditional | Required for Calls and Meetings. |
| Participants | Lookup | Conditional | Users or Persons involved in the meeting/call. |
| Status | Dropdown | Yes | Planned or Done (for calls/meetings). |
| Linked To | Hidden/Lookup| Yes | The Lead/Person this activity belongs to. |

## 6. User Workflow
```text
User opens a Lead Detail page
    ↓
Clicks "Add Note" on the Timeline
    ↓
Fills out Note comment and Title
    ↓
Saves Form
    ↓
Backend validates request
    ↓
Saves to `activities` table with relation to Lead
    ↓
Timeline component dynamically reloads
    ↓
Note appears in chronological order
```

## 7. CRUD Operations
*See [docs/crud/activity-crud.md](../crud/activity-crud.md).*

## 8. API Documentation
```text
Method: GET
Endpoint: /admin/activities
Purpose: Load Activities DataGrid.

Method: POST
Endpoint: /admin/activities/create
Purpose: Create a new activity (from global or detail view).

Method: PUT
Endpoint: /admin/activities/edit/{id}
Purpose: Update existing activity.

Method: DELETE
Endpoint: /admin/activities/{id}
Purpose: Delete an activity.

Method: PUT
Endpoint: /admin/activities/update-status/{id}
Purpose: Mark a scheduled activity as 'Done'.
```

## 9. Database / Data Model
**Tables**: `activities`, `activity_participants`

- `activities`: `id`, `title`, `type` (note/call/meeting/email), `comment`, `schedule_from`, `schedule_to`, `is_done`, `user_id`.
- `activity_participants`: `id`, `activity_id`, `user_id`, `person_id`.

## 10. Relationships
```text
Activity
 ├── belongs to → User (Creator)
 ├── has many → Participants (Users/Persons via activity_participants)
 ├── polymorphic many-to-many → Can be attached to Leads, Persons, Organizations
```
*(Krayin manages these relationships either via direct FKs or pivot tables depending on the exact schema structure; often it's a polymorphic relation for timeline attachments).*

## 11. Business Rules
- "Notes" and "Emails" do not have scheduled times; they happen instantly.
- "Calls" and "Meetings" must have a `schedule_from` and `schedule_to` time.
- Only the creator or an admin can edit an activity.
- An activity must be linked to a parent record (Lead, Person, or Organization) when created from a detail view.

## 12. Permissions and Authorization
- `activities.view`, `activities.create`, `activities.edit`, `activities.delete`.

## 13. Files / Code Locations
- Frontend Views: `packages/Webkul/Admin/src/Resources/views/activities/`
- Controller: `ActivityController.php`
- Repository: `ActivityRepository.php`
- Model: `Activity.php`
