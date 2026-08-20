# Marketing & Campaigns Management

## 1. Feature Name
Marketing (Campaigns)

## 2. What Is This Feature?
Marketing Campaigns allow users to send targeted, mass emails to a specific segment or list of contacts. It leverages email templates and integrates with the backend mailer to handle bulk distribution.

## 3. How Is It Useful?
It helps the sales and marketing teams nurture leads by sending newsletters, promotional offers, or important announcements to many contacts simultaneously without leaving the CRM.

## 4. Users / Roles
- **Admin**: Full access.
- **Marketing/Sales User**: Can view, create, edit, and send campaigns depending on permissions.

## 5. Frontend / UI

### Page / Screen
- **Route**: `/admin/campaigns`
- **Page Purpose**: View and manage email marketing campaigns.
- **Navigation Location**: Main Sidebar -> Marketing -> Campaigns.

### UI Components
- **DataGrid**: Tabular list of campaigns.
- **Filter Sidebar**: Filter by status (Active, Inactive, Completed) or name.
- **Create/Edit Form**: Multi-step or tabbed form for campaign configuration.

### Available User Options
```text
Actions
├── View/Edit Campaign
├── Create Campaign
├── Delete Campaign
└── Mass Delete
```

### Form Fields
| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| Name | Text | Yes | Internal name of the campaign. |
| Subject | Text | Yes | Email subject line. |
| Sender Name | Text | Yes | "From" name. |
| Sender Email| Email | Yes | "From" email address. |
| Email Template | Lookup | Yes | The template (from `email_templates`) to use. |
| Audience/Segment | Dropdown/Multi | Yes | Who will receive this (e.g., specific tags, lead sources, or persons). |
| Status | Boolean | Yes | Active/Inactive. |

## 6. User Workflow
```text
User navigates to Marketing -> Campaigns
    ↓
Clicks "Create Campaign"
    ↓
Fills in Campaign Name, Subject, Sender Info
    ↓
Selects an existing Email Template
    ↓
Selects target audience (e.g., Persons with Tag "Newsletter")
    ↓
Saves Campaign
    ↓
Backend saves to `campaigns` table
    ↓
A Background Job/Command processes the queue to send the emails.
```

## 7. CRUD Operations
*See [docs/crud/campaign-crud.md](../crud/campaign-crud.md).*

## 8. API Documentation
```text
Method: GET
Endpoint: /admin/campaigns
Purpose: Load Campaigns DataGrid.

Method: POST
Endpoint: /admin/campaigns/create
Purpose: Create a new campaign.

Method: PUT
Endpoint: /admin/campaigns/edit/{id}
Purpose: Update existing campaign.

Method: DELETE
Endpoint: /admin/campaigns/{id}
Purpose: Delete a campaign.
```

## 9. Database / Data Model
**Tables**: `campaigns`

- `campaigns`: `id`, `name`, `subject`, `status`, `sender_name`, `sender_email`, `email_template_id`, `created_at`, `updated_at`.

## 10. Relationships
```text
Campaign
 └── belongs to → EmailTemplate
```

## 11. Data Flow
```text
Campaign Form UI
 ↓
CampaignController
 ↓
CampaignRepository -> Saves metadata to DB
 ↓
Artisan Command / Cron Job (e.g., `campaigns:process`)
 ↓
Retrieves Active Campaigns & Target Audience
 ↓
Dispatches Jobs to Laravel Queue
 ↓
Sends via Mailer Configuration (SMTP/Mailgun etc.)
```

## 12. Business Rules
- A campaign must have a valid `email_template_id` before it can be sent.
- Target audience logic is processed server-side based on the selected segmentation criteria.

## 13. Permissions and Authorization
- ACL: `marketing.campaigns.view`, `create`, `edit`, `delete`.
