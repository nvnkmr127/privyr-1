# Frontend Navigation

Krayin's navigation is dynamically generated based on package configurations.

## Sidebar Menu
Each package defines its own sidebar items in a `menu.php` configuration file (e.g., `packages/Webkul/Lead/src/Config/menu.php`).
The `Core` package aggregates these configurations and renders the sidebar.

- **Dashboard**: High-level metrics.
- **Leads**: Pipeline views.
- **Contacts**: Persons and Organizations.
- **Activities**: Timeline of events.
- **Marketing**: Campaigns and Templates.
- **Settings**: Admin configurations (Attributes, Workflows, Users).

## Top Bar
- **Global Search**: Allows searching across Leads, Persons, and Organizations.
- **Quick Create**: A dropdown menu to quickly spawn a Lead, Person, or Activity modal from any page.
- **User Profile**: Logout, account settings.
