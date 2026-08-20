# Services Overview

Services in Krayin are used for complex logic that doesn't belong in a Controller or a Repository.

- **Mailer Services**: Handling the compilation of Email Templates and dispatching them via SMTP.
- **Import/Export Services**: Parsing CSVs and mapping rows to Database columns for DataTransfer.
- **Workflow Evaluators**: Classes that take an Event, load the relevant Workflows, evaluate the Conditions against the entity, and execute the Actions.
