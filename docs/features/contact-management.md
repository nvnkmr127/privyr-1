# Contact Management

## 1. Feature Name
Contact Management (Persons and Organizations)

## 2. What Is This Feature?
Contact Management handles the core entities representing external people and businesses that interact with the company. 
It is split into two primary resources:
1. **Persons**: Individual people, such as clients, point-of-contacts, or leads.
2. **Organizations**: Companies or businesses. A Person usually belongs to an Organization.

## 3. How Is It Useful?
This feature provides a centralized address book and CRM database for all interactions. It allows users to store custom attributes (like industry, job title, phone numbers, and addresses) and view a unified timeline of all activities related to a specific person or organization.

## 4. Users / Roles
- **Admin**: Full access.
- **Sales/Support User**: Can view, create, and edit contacts. Deletion may be restricted based on ACL configurations.

## 5. Frontend / UI

### Page / Screen
- **Routes**: `/admin/contacts/persons` and `/admin/contacts/organizations`
- **Page Purpose**: List and manage all persons and organizations.
- **Navigation Location**: Main Sidebar -> Contacts.

### UI Components
- **DataGrid**: Tabular list of contacts.
- **Filter Sidebar**: Filtering by name, email, organization, etc.
- **Detail View**: A full-page view for a Person or Organization showing their details, linked Leads, linked Organizations/Persons, and an Activity Timeline.

### Available User Options (Person/Organization)
```text
Actions
├── View Detail
├── Create 
├── Edit 
├── Delete 
└── Mass Delete (DataGrid)
```

### Form Fields (Person Create/Edit)
| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| Name | Text | Yes | Full name. |
| Email | Email | Yes (usually) | Contact's email address. |
| Contact Numbers | Text/Repeater | No | Phone numbers. |
| Organization | Lookup | No | Link to an existing Organization. |

*Note: Additional fields are generated dynamically via the Attribute (EAV) system.*

## 6. User Workflow
```text
User navigates to Contacts -> Persons
    ↓
Clicks "Create Person"
    ↓
Fills out Name, Emails, Phones, Organization
    ↓
Saves Form
    ↓
Backend validates request
    ↓
Saves to `persons` table and custom attributes
    ↓
Redirects to Persons list with success message
```

## 7. CRUD Operations
*See [docs/crud/person-crud.md](../crud/person-crud.md) and [docs/crud/organization-crud.md](../crud/organization-crud.md).*

## 8. API Documentation
```text
Method: GET
Endpoint: /admin/contacts/persons
Purpose: Load Persons DataGrid.

Method: POST
Endpoint: /admin/contacts/persons/create
Purpose: Create a new person.

Method: PUT
Endpoint: /admin/contacts/persons/edit/{id}
Purpose: Update existing person.

Method: DELETE
Endpoint: /admin/contacts/persons/{id}
Purpose: Delete a person.
```
*(Similar endpoints exist for `organizations`)*

## 9. Database / Data Model
**Tables**: `persons`, `organizations`

- `persons`: `id`, `name`, `emails`, `contact_numbers`, `organization_id`.
- `organizations`: `id`, `name`, `address`.

## 10. Relationships
```text
Person
 ├── belongs to → Organization (Optional)
 ├── has many → Leads
 ├── has many → Activities
 └── has many → PersonAttributeValues (Custom fields)

Organization
 ├── has many → Persons
 ├── has many → Leads
 ├── has many → Activities
 └── has many → OrganizationAttributeValues (Custom fields)
```

## 11. Business Rules
- If an organization is deleted, associated persons might have their `organization_id` set to null (depending on DB constraint, usually `SET NULL` for loose coupling).
- A person must have a valid name.
- Custom attributes (EAV) defined by the admin will automatically appear on the create/edit forms.

## 12. Permissions and Authorization
- ACL keys: `contacts.persons.view`, `contacts.persons.create`, `contacts.persons.edit`, `contacts.persons.delete`.
- Similar for organizations.

## 13. Files / Code Locations
- Frontend Views: `packages/Webkul/Admin/src/Resources/views/contacts/`
- Controllers: `PersonController.php`, `OrganizationController.php`
- Repositories: `PersonRepository.php`, `OrganizationRepository.php`
- Models: `Person.php`, `Organization.php`
