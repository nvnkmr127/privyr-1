# Authorization

While Authentication confirms *who* the user is, Authorization confirms *what* they are allowed to do.

## Bouncer Integration
Krayin uses Bouncer for its RBAC (Role-Based Access Control) implementation.
See [Roles and Permissions](roles-and-permissions.md) for details on the specific package ACL arrays.

## Record-Level Authorization
While Bouncer handles module-level access (e.g., "Can this user view the Leads page?"), Krayin also implements record-level checks via Global Scopes or Repository filters.
- **Example**: If a user does not have the "View All Leads" permission, the `LeadRepository` will automatically scope queries to `where('user_id', auth()->id())` so they only see their own assigned leads.
