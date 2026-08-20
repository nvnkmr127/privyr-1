# API Overview

Krayin CRM uses Laravel's routing system to expose APIs. While much of the admin panel is server-rendered Blade templates, DataGrids and Kanban boards heavily rely on internal JSON endpoints.

## Endpoint Structure
Internal API endpoints are typically nested under the `/admin` prefix.
- `GET /admin/{entity}/get` -> Returns paginated JSON for DataGrids.
- `POST /admin/{entity}/create` -> Handles form submission.
- `PUT /admin/{entity}/edit/{id}` -> Handles updates.
- `DELETE /admin/{entity}/{id}` -> Handles deletion.

## External API (Public)
Public endpoints (like WebForm submissions) are typically exposed via `routes/api.php` or a dedicated package route file (e.g., `/api/web-form/{id}`). These do not require session authentication but rely on embedded form tokens or CORS rules.

## Authentication
Internal APIs rely on standard Laravel Session cookies (not Bearer tokens). The `web` middleware group applies to these routes, meaning CSRF tokens are required for POST/PUT/DELETE requests.
