# Email Templates Management

## 1. Feature Name
Email Templates

## 2. What Is This Feature?
Email Templates allow users to create and manage reusable HTML and plain-text templates for emails. These templates are used extensively by Marketing Campaigns and Automation Workflows to send consistent messaging.

## 3. How Is It Useful?
It saves time by removing the need to type out emails from scratch. Users can use placeholders (variables) in the templates that will be dynamically replaced with actual data (like `{person.name}`) when the email is sent.

## 4. Users / Roles
- **Admin**: Full access.
- **Marketing/Sales User**: Can view, create, edit, and use templates.

## 5. Frontend / UI

### Page / Screen
- **Route**: `/admin/settings/email-templates`
- **Page Purpose**: List and manage all email templates.
- **Navigation Location**: Settings -> Automation -> Email Templates.

### UI Components
- **DataGrid**: Tabular list of templates.
- **Form View**: A form to create the template, featuring a Rich Text Editor (e.g., TinyMCE) and placeholder variable selectors.

### Form Fields
| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| Name | Text | Yes | Internal name of the template. |
| Subject | Text | Yes | Default email subject line. |
| Content | Rich Text | Yes | The HTML body of the email. |

## 6. User Workflow
```text
User navigates to Settings -> Email Templates
    ↓
Clicks "Create Email Template"
    ↓
Enters Name, Subject, and types the Content
    ↓
Inserts placeholders using the UI selector (e.g., {lead.title})
    ↓
Saves Template
    ↓
Template is now available in dropdowns when creating Campaigns or Workflows.
```

## 7. CRUD Operations
*See [docs/crud/email-template-crud.md](../crud/email-template-crud.md).*

## 8. API Documentation
```text
Method: GET
Endpoint: /admin/settings/email-templates
Purpose: Load Templates DataGrid.

Method: POST
Endpoint: /admin/settings/email-templates/create
Purpose: Create a new template.

Method: PUT
Endpoint: /admin/settings/email-templates/edit/{id}
Purpose: Update existing template.

Method: DELETE
Endpoint: /admin/settings/email-templates/{id}
Purpose: Delete a template.
```

## 9. Database / Data Model
**Tables**: `email_templates`

- `email_templates`: `id`, `name`, `subject`, `content`, `created_at`, `updated_at`.

## 10. Relationships
```text
EmailTemplate
 ├── has many → Campaigns
 └── referenced by → Workflows (Action Payload)
```

## 11. Data Flow
- Very simple CRUD flow. 
- The real complex data flow happens when the template is *compiled*. A Mailer Service takes the `content`, parses the placeholders (e.g., Regex match for `{.*}`), retrieves the actual entity data from the database, string-replaces the variables, and dispatches the final HTML to the Queue.

## 12. Business Rules
- Templates must have a Subject and Content.
- Deleting a template that is currently active in a Campaign or Workflow will break that Campaign/Workflow (unless protected by a foreign key constraint).

## 13. Files / Code Locations
- Frontend Views: `packages/Webkul/Admin/src/Resources/views/settings/email_templates/`
- Controller: `EmailTemplateController.php`
- Repository: `EmailTemplateRepository.php`
- Model: `EmailTemplate.php`
