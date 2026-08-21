# Tags Management

## 1. Feature Name
Tags Management

## 2. What Is This Feature?
Tags provide a flexible, free-form way to categorize and label records (Leads) across the CRM.

## 3. How Is It Useful?
Instead of creating rigid custom attributes for every minor categorization, users can quickly create and apply tags (e.g., "VIP", "Needs Follow-up", "Cold"). Tags are color-coded for quick visual identification on Kanban boards and Datagrids.

## 4. Users / Roles
- **Admin**: Full access to create/delete global tags.
- **User**: Can apply existing tags to records, and sometimes create new ones on the fly depending on settings.

## 5. Frontend / UI
- **Tag Overlay**: A popover/dropdown attached to entities allowing users to search, select, or create a tag.
- **Settings Page**: `/admin/settings/tags` for managing all tags centrally.

## 6. Database / Data Model
**Tables**: `tags`, `taggables` (pivot)

- `tags`: `id`, `name`, `color`, `user_id`.
- `taggables`: `tag_id`, `taggable_id`, `taggable_type` (Polymorphic relationship).

## 7. Data Flow
- When a tag is added to a Lead, a record is inserted into `taggables` with `taggable_type = 'Webkul\Lead\Models\Lead'` and the respective IDs. This allows the Tag system to be completely agnostic of the entity it attaches to.
