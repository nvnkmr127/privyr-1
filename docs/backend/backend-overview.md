# Backend Architecture & Overview

This document explains the backend architecture of Krayin CRM.

## 1. Framework
- **Laravel**: Krayin uses the Laravel PHP framework, relying heavily on its Service Container, Routing, Eloquent ORM, and Events systems.

## 2. Package-Based Modularity
Unlike a standard monolithic Laravel app where all code lives in `app/`, Krayin isolates features into specific packages under `packages/Webkul/` (e.g., `packages/Webkul/Lead`, `packages/Webkul/Contact`).
This allows modular development and easier overriding.

### Standard Package Structure:
```text
packages/Webkul/{Module}/
 ├── src/
 │   ├── Contracts/        # Interfaces for Repositories
 │   ├── Database/         # Migrations and Seeders
 │   ├── Http/
 │   │   ├── Controllers/  # Web and API controllers
 │   │   ├── Requests/     # Form Requests (Validation)
 │   │   └── routes.php    # Package specific routes
 │   ├── Models/           # Eloquent Models
 │   ├── Providers/        # Service Providers (Module registration, Event binding)
 │   └── Repositories/     # Database interaction layer
```

## 3. The Repository Pattern
Controllers in Krayin **do not** query Eloquent models directly. Instead, they inject a Repository (e.g., `LeadRepository`).
- **Why?** It abstracts the database layer, making it easier to attach overarching logic (like EAV attribute saving) during a save operation without cluttering the controller.

## 4. Event-Driven Architecture
Krayin is highly event-driven, which powers its Automation/Workflow system.
- Before any CRUD operation, an event is fired (e.g., `Event::dispatch('lead.create.before')`).
- After the operation, another event is fired (`lead.create.after`).
- This allows unrelated modules (like Emails or Workflows) to "listen" to a Lead being created and trigger actions without the `LeadController` knowing about them.

## 5. Background Jobs (Queues)
Tasks that take a long time are pushed to Laravel Queues (usually Redis or Database queues).
- **Campaigns**: Sending thousands of emails.
- **Webhooks**: Dispatching payloads to external systems.

## 6. Validation (Form Requests)
Validation logic is isolated into `FormRequest` classes (located in `Http/Requests/`). This ensures the Controller only receives clean, validated data. If validation fails, Laravel automatically redirects back with error messages (or returns a 422 JSON response for XHR).
