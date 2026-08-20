# Background Jobs

Krayin relies on Laravel's Queue system to handle long-running or asynchronous tasks.

## Known Jobs
1. **Email Sending**: When a Campaign is executed, or an Automation Workflow triggers an email, the actual SMTP dispatch is pushed to a Job to prevent the web request from hanging.
2. **Webhooks**: If the system needs to send data to an external API, it is queued to handle retries and timeouts gracefully.

## Queue Workers
To process these background jobs in a production environment, a worker process must be running:
```bash
php artisan queue:work
```
Or managed via a process monitor like Supervisor.
