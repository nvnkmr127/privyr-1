# Feature Index

This document provides a comprehensive inventory of all major features identified within the Krayin CRM system.

| Feature | Module | Frontend | Backend | Database | CRUD | Permissions | Status |
| ------- | ------ | -------- | ------- | -------- | ---- | ----------- | ------ |
| **Leads Management** | Lead | Kanban/List | `LeadController` | `leads` | Full | Yes | Implemented |
| **Pipelines** | Lead | Settings | `PipelineController` | `lead_pipelines` | Full | Yes | Implemented |
| **Stages** | Lead | Settings | `StageController` | `lead_pipeline_stages` | Full | Yes | Implemented |
| **Sources** | Lead | Settings | `SourceController` | `lead_sources` | Full | Yes | Implemented |
| **Types** | Lead | Settings | `TypeController` | `lead_types` | Full | Yes | Implemented |
| **Persons** | Contact | List | `PersonController` | `persons` | Full | Yes | Implemented |
| **Organizations** | Contact | List | `OrganizationController`| `organizations`| Full | Yes | Implemented |
| **Activities** | Activity | List | `ActivityController` | `activities` | Full | Yes | Implemented |
| **Users (Staff)** | User | Settings | `UserController` | `users` | Full | Yes | Implemented |
| **Roles** | User | Settings | `RoleController` | `roles` | Full | Yes | Implemented |
| **Groups/Teams** | User | Settings | `GroupController` | `groups` | Full | Yes | Implemented |
| **Attributes (Custom Fields)** | Attribute | Settings | `AttributeController`| `attributes` | Full | Yes | Implemented |
| **Automations (Workflows)** | Automation | List | `WorkflowController` | `workflows` | Full | Yes | Implemented |
| **Tags** | Tag | Overlay | `TagController` | `tags` | Full | Yes | Implemented |
| **Email Templates** | EmailTemplate| Settings | `EmailTemplateController`| `email_templates` | Full | Yes | Implemented |
| **Web Forms** | WebForm | List | `WebFormController` | `web_forms` | Full | Yes | Implemented |
| **Campaigns** | Marketing | List | `CampaignController` | `campaigns` | Full | Yes | Implemented |

*Note: Minor features like bulk actions, specific API endpoints, and system settings are embedded within their respective module documentation.*
