# User & Role Management

## 1. Feature Name
User & Role Management

## 2. What Is This Feature?
This feature manages the internal staff (Users) who have access to the CRM, the Roles (ACL permissions) assigned to them, and the Groups/Teams they belong to. 

## 3. How Is It Useful?
It provides the foundation for system security and data segregation. Administrators use it to ensure that sales reps only see their own leads, or that only managers can delete records.

## 4. Users / Roles
- **Admin**: Full access. Typically the only role allowed to access this Settings section.
- **User**: Users cannot manage other users. They can only edit their own profile settings.

## 5. Frontend / UI

### Page / Screen
- **Routes**: `/admin/settings/users`, `/admin/settings/roles`, `/admin/settings/groups`
- **Page Purpose**: Manage staff access.
- **Navigation Location**: Settings -> User -> (Users, Roles, Groups).

### UI Components
- **DataGrid**: Tabular list of Users, Roles, or Groups.
- **Role Permission Tree**: A nested checkbox UI inside the Role creation form that visually represents all ACL keys (e.g., `leads.view`, `contacts.create`).

### Form Fields (Create User)
| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| Name | Text | Yes | Staff member's name. |
| Email | Email | Yes | Login email. |
| Password | Password | Yes | Login password. |
| Role | Dropdown | Yes | Links to a `Role`. |
| Groups | Multi-select | No | Links to `Groups`. |
| Status | Boolean | Yes | Active/Inactive. |

## 6. User Workflow (Creating a Staff Member)
```text
Admin navigates to Settings -> Users
    ↓
Clicks "Create User"
    ↓
Fills out Name, Email, Password
    ↓
Selects "Sales Rep" from the Role dropdown
    ↓
Saves User
    ↓
Backend validates request (e.g., email uniqueness)
    ↓
Saves to `users` table
    ↓
New staff member can now log in using those credentials.
```

## 7. CRUD Operations
*See [docs/crud/user-crud.md](../crud/user-crud.md).*

## 8. API Documentation
```text
Method: GET
Endpoint: /admin/settings/users
Purpose: Load Users DataGrid.

Method: POST
Endpoint: /admin/settings/users/create
Purpose: Create a new user.

Method: PUT
Endpoint: /admin/settings/users/edit/{id}
Purpose: Update existing user.

Method: DELETE
Endpoint: /admin/settings/users/{id}
Purpose: Delete a user.
```

## 9. Database / Data Model
**Tables**: `users`, `roles`, `groups`, `user_groups`

- `users`: `id`, `name`, `email`, `password`, `role_id`, `status`.
- `roles`: `id`, `name`, `description`, `permissions` (JSON array of ACL strings).
- `groups`: `id`, `name`, `description`.
- `user_groups`: `user_id`, `group_id`.

## 10. Relationships
```text
User
 ├── belongs to → Role
 └── belongs to many → Groups (via user_groups)
```

## 11. Business Rules
- A User must have a Role.
- The root Admin user cannot be deleted.
- If a Role is deleted, users assigned to that role might lose access or deletion might be blocked.

## 12. Permissions and Authorization
- Admin-level ACLs: `settings.users.users.view`, `settings.users.roles.view`, etc.
