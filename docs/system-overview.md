# System Overview

## 1. System Purpose

Krayin CRM is an open-source Customer Relationship Management (CRM) platform built on the Laravel PHP framework.
The system is designed to help businesses manage their leads, contacts, activities, and marketing automation from a central dashboard.

**Main User Types:**
- **Admin**: Full access to all modules, configurations, and user management.
- **Sales/Marketing Users**: Access to leads, contacts, activities, and communication tools.
- **Support**: Interaction with tickets (if integrated).

**Main Business Objectives:**
- Centralize customer information (Leads).
- Track interactions and activities (Emails, Calls, Meetings).
- Automate workflows and marketing.
- Manage sales pipelines and performance.

## 2. Architecture

**Frontend Architecture:**
- Blade templates (Laravel's default templating engine) for server-side rendering.
- Vue.js (integrated via Laravel Mix/Vite) for interactive components, especially in complex views like DataGrids and Kanban boards.

**Backend Architecture:**
- **Laravel Framework (v11/12)**: Handles routing, requests, middleware, and business logic.
- **Modular Package System**: Krayin splits its functionality into specific packages inside `packages/Webkul/`, keeping core concerns separated.
- **Repository Pattern**: Data access is abstracted through Repository classes rather than using Eloquent models directly in controllers.
- **Contracts**: Interfaces are bound to concrete implementations in Service Providers.

**Database Architecture:**
- Relational database (MySQL/PostgreSQL) managed through Laravel Migrations and Schema Builder.
- Eloquent ORM is used for modeling relationships.

**Other Components:**
- **Authentication**: Laravel session-based authentication with role-based access control (RBAC).
- **Background Jobs**: Handled via Laravel Queues for sending emails and processing automations.
- **File/Storage**: Local storage and potentially cloud integration (S3) via Laravel's Storage facade.

## 3. Major Modules

Based on the system's package structure, the major modules are:

1. **Activity**: Manages calls, meetings, notes, and tasks related to leads/contacts.
2. **Admin**: The core UI structure, layouts, and global settings for the admin panel.
3. **Attribute**: EAV (Entity-Attribute-Value) system for custom fields on leads, contacts, etc.
4. **Automation**: Workflows and rule-based triggers.
5. **Core**: Base functionality, locale, currency, and system-wide configurations.
6. **DataGrid**: Reusable table component for listing, filtering, sorting, and pagination.
7. **Email / EmailTemplate**: Integration for sending and tracking emails and template management.
8. **Lead**: The core sales pipeline module (managing leads, stages, and pipelines).
9. **Tag**: Categorization and labeling system.
10. **User**: Authentication, roles, permissions, and team management.
11. **WebForm**: Capture leads from external websites.

## 4. Module Dependencies

*Note: This is a high-level representation based on the architecture.*

```text
Lead
 ├── User (Owner assigned)
 ├── Tag (Labeling)
 ├── Attribute (Custom fields)
 └── Activity (Interactions)

Activity
 ├── User (Participant/Creator)
 └── Lead (Context)

Automation
 ├── Lead (Triggers)
 ├── Email (Actions)
 └── Core (Conditions)
```
