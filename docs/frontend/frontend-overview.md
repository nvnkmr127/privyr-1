# Frontend Architecture & Overview

This document outlines the frontend structure, rendering strategies, and component ecosystem used in Krayin CRM.

## 1. Core Technologies
- **Blade Templating**: Laravel's default server-side rendering engine. Most structural HTML is generated server-side.
- **Vue.js**: Integrated via Laravel Mix or Vite (depending on version). Vue is heavily used for interactive components like the Kanban board, dynamic forms (EAV attributes), and DataGrids.
- **Axios**: Used for all XHR/AJAX requests within Vue components.

## 2. Layout Structure
The main admin panel is defined in the `Admin` package, typically located in `packages/Webkul/Admin/src/Resources/views/layouts/`.

### Master Layout components:
- **Sidebar**: Dynamic navigation generated from configuration (`menu.php` files in packages).
- **Header**: Global search, Quick Create dropdown, user profile, and notifications.
- **Content Area**: The main yield area for module-specific views.

## 3. Key UI Components

### DataGrid Component
The most heavily reused component. It receives a configuration object from the backend (usually a PHP class extending a base `DataGrid` class) and renders:
- Tabular data
- Search and filtering UI
- Pagination controls
- Mass Action checkboxes
- Row Action buttons (Edit/Delete)

### Kanban Component (Leads)
A specialized drag-and-drop board for visualizing sales pipelines. 
- Uses Vue.draggable (or similar) to handle stage transitions.
- Dispatches AJAX requests immediately upon card drop to update the database.

### Dynamic Attribute Forms
For entities like Leads and Contacts, forms are not hardcoded. A Blade directive or Vue component loops over the configured EAV Attributes for that entity type and dynamically renders text inputs, selects, datepickers, or textareas based on the attribute's `type` column.

## 4. UI Workflows & State Management
- **State Management**: While complex pages (like Kanban) may use Vuex or local component state, most standard pages rely on server-side rendering combined with localized Vue state for things like modals or dropdowns.
- **Modals/Drawers**: Heavily used for Create/Edit operations to prevent navigating away from list views.
- **Validation**: 
  - Frontend: VeeValidate (or similar Vue plugin) is often used to catch errors before submission.
  - Backend validation errors are caught via Axios interceptors and mapped back to the UI fields.

## 5. Directory Structure Reference
Frontend assets are usually located in:
- `packages/Webkul/Admin/src/Resources/assets/js/` (Vue components, entry points)
- `packages/Webkul/Admin/src/Resources/assets/sass/` (Styling)
- `packages/Webkul/Admin/src/Resources/views/` (Blade templates)
