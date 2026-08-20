# Database Overview

This document provides a high-level summary of the Krayin CRM database architecture.

## Overview
Krayin CRM uses a relational database model (compatible with MySQL and PostgreSQL) to manage all its domain entities. The database structure is highly normalized, utilizing foreign keys with cascading actions where appropriate to maintain referential integrity.

## Key Subsystems

### 1. Leads & Sales Pipeline
- **`leads`**: The central table storing all lead information, statuses, and expected values.
- **`lead_pipelines` & `lead_pipeline_stages`**: Configurable stages a lead passes through.
- **`lead_sources` & `lead_types`**: Categorization metadata for leads.
- **`lead_tags`**: Pivot table for many-to-many relationship with tags.

### 2. Contacts (Persons & Organizations)
- **`persons`**: Individual contacts (e.g., John Doe).
- **`organizations`**: Companies (e.g., Acme Corp). Persons typically belong to an organization.

### 3. Activities
- **`activities`**: Logs all interactions (Calls, Meetings, Emails, Notes).
- **`activity_participants`**: Links users and persons to an activity.

### 4. Users & Access Control
- **`users`**: System staff and administrators.
- **`roles`**: RBAC roles defining system permissions.
- **`groups`**: Teams/departments for users.

### 5. Attributes (EAV System)
- **`attributes`**: Defines custom fields across various entities (leads, persons, organizations).
- **`attribute_options`**: Dropdown choices for attributes.
- **`{entity}_attribute_values`**: (e.g., `lead_attribute_values`) Stores the actual data for custom fields.

### 6. Automation & Workflows
- **`workflows`**: Defined rule sets.
- **`workflow_actions`**: Actions triggered by the workflows.

### 7. Marketing & Emails
- **`campaigns`**: Email/marketing campaigns.
- **`email_templates`**: Reusable templates for communication.
- **`emails`**: Logged emails sent through the system.

## Schema Details
For a detailed mapping of every table and column, see [Entities](entities.md).
For a detailed mapping of all foreign keys, see [Relationships](relationships.md).
