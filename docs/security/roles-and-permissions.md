# Roles and Permissions (Security)

This document explains the Access Control List (ACL) and Authorization mechanisms in Krayin CRM.

## 1. Authentication
Krayin uses Laravel's standard session-based authentication for the web interface.
- Guards: The `admin` guard is used for CRM staff users.
- User Model: `Webkul\User\Models\User`

## 2. Authorization (Bouncer / ACL)
Krayin implements a Role-Based Access Control (RBAC) system. A User is assigned to a `Role`, and that `Role` possesses an array of `Permissions`.

### The `acl.php` configuration
Each package (e.g., Lead, Contact, Activity) defines its available permissions in a `Config/acl.php` file.
Example structure:
```php
[
    'key'   => 'leads',
    'name'  => 'Leads',
    'route' => 'admin.leads.index',
    'sort'  => 1
],
[
    'key'   => 'leads.create',
    'name'  => 'Create',
    'route' => 'admin.leads.create',
    'sort'  => 1
],
```
The `key` (e.g., `leads.create`) is the unique string used throughout the application to check access.

## 3. How Permissions are Enforced

### Route Middleware
Routes are protected using the `BouncerMiddleware`. If a route definition contains a `'bouncher' => 'leads.create'` key, the middleware intercepts the request.
- It checks if the authenticated user's Role has the `leads.create` permission in its JSON array.
- If not, a `403 Unauthorized` error is thrown (or a redirect to dashboard with an error message).

### View Layer (Blade)
UI elements (like a "Delete" button) are hidden from users who don't have permission using a custom Blade directive:
```blade
@if (bouncer()->hasPermission('leads.delete'))
    <button>Delete</button>
@endif
```

### Controller Level
Sometimes fine-grained checks happen in the controller:
```php
if (! bouncer()->hasPermission('settings.users.users.edit')) {
    abort(403);
}
```

## 4. Super Admin
The root user (ID 1) or a user assigned the hardcoded `Administrator` role usually bypasses all ACL checks, possessing implicit access to every module and route.
