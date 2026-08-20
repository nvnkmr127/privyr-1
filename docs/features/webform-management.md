# WebForm Management

## 1. Feature Name
WebForm Management (Lead Capture)

## 2. What Is This Feature?
WebForms allow administrators to create embeddable HTML forms or public URLs that external users (website visitors) can fill out. When submitted, the form automatically creates a new Lead or Person in the CRM.

## 3. How Is It Useful?
It acts as the bridge between a company's public website and the CRM. Instead of manually entering "Contact Us" emails, submissions go directly into the sales pipeline.

## 4. Users / Roles
- **Admin**: Full access.
- **User**: View access depending on permissions.

## 5. Frontend / UI

### Page / Screen
- **Route**: `/admin/settings/web-forms`
- **Page Purpose**: Manage lead capture forms.
- **Navigation Location**: Settings -> Automation -> Web Forms.

### UI Components
- **DataGrid**: List of configured forms.
- **Form Builder UI**: A drag-and-drop or select interface to choose which Attributes (fields) should appear on the public form.
- **Embed Code Modal**: Provides the `<script>` or `<iframe>` tag to embed on a website.

### Form Fields (Create WebForm)
| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| Title | Text | Yes | Internal name. |
| Description | Textarea | No | Form description. |
| Pipeline / Stage| Lookup | Yes | Where the generated lead should land. |
| Owner | Lookup | No | Who gets assigned the lead. |
| Form Fields | Repeater | Yes | The attributes to show on the form (e.g., Name, Email, Custom fields). |
| Return URL | URL | No | Where to redirect the user after submission. |

## 6. User Workflow (Visitor submitting a form)
```text
Visitor navigates to company website
    ↓
Fills out embedded WebForm
    ↓
Clicks Submit
    ↓
POST request sent to public Krayin endpoint (`/api/web-form/{id}`)
    ↓
Backend validates request
    ↓
Creates Person (if doesn't exist) -> Creates Lead -> Links to Person
    ↓
Assigns to specified Owner and Stage
    ↓
Redirects visitor to Return URL
```

## 7. CRUD Operations
*See [docs/crud/webform-crud.md](../crud/webform-crud.md).*

## 8. Database / Data Model
**Tables**: `web_forms`, `web_form_fields`

- `web_forms`: Config data (title, pipeline_id, return_url).
- `web_form_fields`: Which attributes are included in the form and if they are required.

## 9. Business Rules
- Public submission endpoints do not require authentication but may have CSRF/CORS protections or hidden tokens.
- If a person already exists with the submitted email, the system usually attaches the new lead to the existing person to prevent duplication.
