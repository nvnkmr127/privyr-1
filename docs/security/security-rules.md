# Security Rules

General security principles enforced across Krayin CRM:

## 1. CSRF Protection
All POST, PUT, and DELETE routes are protected by Laravel's `VerifyCsrfToken` middleware. Forms must include `@csrf` directives, and Axios requests automatically append the token via the `X-CSRF-TOKEN` header.

## 2. Mass Assignment Protection
Models use the `$fillable` property to protect against mass-assignment vulnerabilities. Only explicitly allowed fields can be inserted via `Model::create($request->all())`.

## 3. SQL Injection Prevention
By utilizing Eloquent ORM and the Query Builder for all database interactions, PDO parameter binding is used automatically, preventing SQL injection.

## 4. XSS Prevention
Blade templating `{{ $variable }}` automatically runs `htmlspecialchars` to escape data before rendering. If raw HTML is needed (like in Email Templates), it must be explicitly trusted or sanitized.
