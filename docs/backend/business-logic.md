# Business Logic Rules

The business logic in Krayin is distributed across:

1. **Form Requests**: Define what data is strictly required before hitting the DB.
2. **Database Constraints**: Foreign keys ensure referential integrity (e.g., deleting a User cannot happen if they own Leads, or it sets the Lead owner to NULL).
3. **Event Listeners**: Cross-module business logic (like updating a Campaign's status when an Email bounces) is handled via Event Listeners.
4. **Repositories**: Saving logic, especially regarding the complex EAV Attribute values, is handled inside the Repositories' `create` and `update` methods.
