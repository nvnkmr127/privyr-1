# Authentication

Krayin CRM uses Laravel's native session-based authentication system.

## Guards and Providers
- **Guard**: `admin`
- **Provider**: `admin` (maps to the `users` table via the `Webkul\User\Models\User` model).

## Login Flow
1. User navigates to `/admin/login`.
2. Submits email and password.
3. `SessionController@store` authenticates the credentials against the `users` table.
4. If successful, session is regenerated and user redirects to `/admin/dashboard`.

## Session Management
- Sessions are stored according to `config/session.php` (file, database, or redis).
- CSRF protection is active on all web routes.

## Password Reset
- Handled by standard Laravel password broker logic (ForgotPasswordController, ResetPasswordController).
- Sends email via configured mailer.
