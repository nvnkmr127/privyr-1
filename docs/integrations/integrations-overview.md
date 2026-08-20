# Integrations

This directory contains documentation for external services Krayin connects with.

## Known Integrations
Based on the core packages, Krayin primarily integrates with:

1. **Email Service Providers (SMTP / Mailgun / SES)**
   - Configured via `.env` (MAIL_MAILER).
   - Used for Campaigns and Workflows.
   
2. **Third-party Webhooks** (Potential via Workflows)
   - Automations can potentially trigger HTTP requests to external services depending on specific package extensions.

*Note: If specific third-party packages (like Twilio, Stripe, or Google Calendar) are installed in `packages/`, they should be documented here.*
