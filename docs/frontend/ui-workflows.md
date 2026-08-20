# UI Workflows

## The Create Flow (Drawer)
Instead of taking the user to a new page (e.g., `/leads/create`), the UI opens a right-side Drawer component.
This preserves the user's context (they can still see the DataGrid behind the overlay).

## The Kanban Drop Flow
1. User clicks and drags a Lead Card.
2. The UI enters a dragging state (CSS updates).
3. User drops the card in a new column.
4. Vue immediately updates the local array to reflect the drop (Optimistic UI).
5. An Axios `PUT` request fires in the background to update the database.
6. If the request fails, the card snaps back to its original column and an error toast is displayed.
