# Frontend Components

Krayin utilizes Vue.js components for highly interactive elements.

## Reusable Vue Components
- **`datagrid`**: Handles async data fetching, pagination, and bulk selection.
- **`kanban-board`**: Wraps a draggable list library, emitting events when items switch stages.
- **`activity-timeline`**: Iterates over a chronological list of notes, calls, and meetings. Provides inline forms to add new activities.
- **`attribute-input`**: A dynamic component that takes an `attribute` object and renders the correct HTML input (e.g., `<select>`, `<input type="date">`, `<textarea>`).
- **`modal` / `drawer`**: Slide-out panels used for Create/Edit forms to prevent page reloading.
