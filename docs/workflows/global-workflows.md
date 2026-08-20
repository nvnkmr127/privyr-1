# Global Workflows

This document tracks end-to-end workflows that span multiple modules.

## 1. Lead Generation to Won Deal
1. **Capture**: A visitor submits a **WebForm**.
2. **Creation**: The system creates a **Person** and a **Lead**, linking them together.
3. **Automation**: A **Workflow** triggers, sending a welcome email to the Person and an internal notification to the Sales Manager.
4. **Assignment**: The manager views the **DataGrid** and assigns the Lead to a Sales Rep (**User**).
5. **Activity Tracking**: The Sales Rep logs a Call (**Activity**) and schedules a Meeting.
6. **Progression**: The Rep drags the Lead across the **Kanban** board to "Negotiation".
7. **Closing**: The Rep updates the Lead status to "Won", updating the expected revenue.

## 2. Marketing Campaign Flow
1. **Segmentation**: Admin filters **Persons** by specific **Tags**.
2. **Templating**: Admin creates an **Email Template** with placeholders.
3. **Execution**: Admin creates a **Campaign**, attaches the template and audience.
4. **Queueing**: The backend **Job** processes the campaign and dispatches emails via SMTP.
