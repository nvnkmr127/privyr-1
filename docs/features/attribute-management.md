# Attributes Management (EAV)

## 1. Feature Name
Attributes Management (Custom Fields)

## 2. What Is This Feature?
Attributes represent the Entity-Attribute-Value (EAV) system in Krayin. It allows administrators to dynamically add new custom fields (text, date, dropdown, boolean, file) to core entities like Leads, Persons, and Organizations without requiring database schema changes (migrations) for every new field.

## 3. How Is It Useful?
Every business tracks different data. A real estate firm might want a "Property Size" field on a Lead, while a software company might want a "Current Tech Stack" field. Attributes allow the CRM to adapt to any business model.

## 4. Users / Roles
- **Admin**: Full access to create/edit attributes.
- **User**: No access to manage attributes. Users only *interact* with attributes when filling out forms for Leads or Contacts.

## 5. Frontend / UI

### Page / Screen
- **Route**: `/admin/settings/attributes`
- **Page Purpose**: View and manage all custom fields.
- **Navigation Location**: Settings -> Automation/System -> Attributes.

### UI Components
- **DataGrid**: Tabular list of attributes.
- **Form View**: A form to define the attribute type, validation, and options (if it's a dropdown/multiselect).

### Form Fields (Create Attribute)
| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| Code | Text | Yes | Internal machine-readable name (e.g., `property_size`). |
| Type | Dropdown | Yes | Text, Textarea, Price, Boolean, Select, Multiselect, Datetime, Date, Image, File. |
| Entity Type | Dropdown | Yes | Lead, Person, Organization. |
| Label | Text | Yes | The user-facing name. |
| Is Required | Boolean | Yes | Does the form require this field? |
| Is Unique | Boolean | Yes | Must the value be unique across all records? |
| Input Validation | Dropdown | No | Email, URL, Numeric, etc. |

## 6. User Workflow (Admin creating a field)
```text
Admin navigates to Settings -> Attributes
    ↓
Clicks "Create Attribute"
    ↓
Selects "Lead" as Entity Type, "Select" as Type
    ↓
Adds Options ("Small", "Medium", "Large")
    ↓
Saves Attribute
    ↓
Backend saves to `attributes` and `attribute_options` tables
    ↓
Next time a user clicks "Create Lead", the new dropdown field appears dynamically on the form.
```

## 7. CRUD Operations
*See [docs/crud/attribute-crud.md](../crud/attribute-crud.md).*

## 8. Database / Data Model
**Tables**: `attributes`, `attribute_options`, `{entity}_attribute_values` (e.g., `lead_attribute_values`)

- `attributes`: Core configuration (code, type, entity_type).
- `attribute_options`: For dropdown/multiselect values.
- `lead_attribute_values`: The actual data saved for a specific lead. Columns: `id`, `lead_id`, `attribute_id`, `text_value`, `boolean_value`, `integer_value`, `float_value`, `datetime_value`, `date_value`, `json_value`.

## 9. Data Flow
- When a Lead form is rendered, the UI fetches all `attributes` where `entity_type = 'leads'`.
- When the Lead is saved, the backend loops through the submitted data. It saves the static fields (title, value) to the `leads` table, and the dynamic fields to `lead_attribute_values` using the correct column type based on the attribute configuration.
