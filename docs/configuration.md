# Configuration

This document outlines the main configuration files and environment settings for Krayin CRM.

## 1. Environment Variables (`.env`)
The primary configuration for deployments is handled via the `.env` file at the project root.

Important keys:
- `APP_ENV`: local/production
- `APP_DEBUG`: true/false (must be false in production)
- `APP_URL`: The base URL of the CRM.
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Database credentials.
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`: SMTP settings required for Campaigns, Automations, and password resets.

## 2. Package Configuration Files
Each Krayin package can publish its own configuration files. These typically reside in `packages/Webkul/{PackageName}/src/Config/`.
They are merged with the application's global configuration during boot.

- **`acl.php`**: Defines the access control list structure for the package.
- **`menu.php`**: Defines the sidebar navigation items injected into the admin panel.

## 3. UI/Runtime Configuration (Settings)
Many configurations are handled dynamically through the Admin UI rather than flat files. This is accessed via **Settings**:
- **Pipelines & Stages**: `lead_pipelines` table.
- **Sources & Types**: `lead_sources` table.
- **Custom Fields (Attributes)**: `attributes` table.
- **Automations**: `workflows` table.

## 4. Dependencies & Integrations
Integrations are typically configured via `.env` (like Mailgun/SMTP credentials). If custom payment gateways or external telephony integrations (like Twilio) are added, their keys must be added to `.env` and loaded via `config/services.php`.
