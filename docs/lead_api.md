# Lead API and Webhook Documentation

## Lead Ingestion API

### Endpoint
`POST /api/v1/leads`

### Authentication
Uses a Bearer Token.
`Authorization: Bearer <your_api_token>`

### Rate Limits
Rate limited to 60 requests per minute per IP.

### Headers
- `Authorization: Bearer <your_api_token>`
- `Content-Type: application/json`
- `Accept: application/json`
- `Idempotency-Key: <unique_string>` (Optional, highly recommended)

### Request Body (JSON)

#### Required Fields
- `origin` (string): The origin of the lead (e.g., 'facebook', 'website').

#### Optional Fields
- `name` (string): Name of the lead.
- `email` (string): Email address.
- `phone` (string): Phone number.
- `source` (string): Specific source name.
- `campaign` (string): Campaign name.
- `medium` (string): Medium (e.g., 'cpc').
- `content` (string): Ad content.
- `term` (string): Search term.
- `landing_page` (string): Landing page URL.
- `form` (string): Form ID.
- `external_source` (string): Name of the external system.
- `external_id` (string): Unique ID in the external system.
- `pipeline` (integer): ID of the pipeline.
- `stage` (integer): ID of the pipeline stage.
- `owner` (integer): ID of the assigned user.
- `metadata` (object): Key-value pair of additional data.

### Example Request
```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "+1234567890",
    "origin": "website",
    "source": "contact_form",
    "external_source": "wpforms",
    "external_id": "12345",
    "campaign": "summer_promo"
}
```

### Responses
**Success (201 Created)**
```json
{
    "success": true,
    "lead_id": 45,
    "external_id": "wpforms_12345",
    "source": "contact_form",
    "status": "created"
}
```

**Duplicate (409 Conflict)**
```json
{
    "success": false,
    "message": "Duplicate lead",
    "match_type": "duplicate"
}
```

**Validation Error (422 Unprocessable Entity)**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email must be a valid email address."]
    }
}
```

### Idempotency
If you include an `Idempotency-Key` header, repeated requests with the same key within 24 hours will return the same response without creating a duplicate lead.

## Webhooks

### Endpoint
`POST /api/v1/webhooks/leads`

### Authentication
Requires signed requests to prevent spoofing and replay attacks.
Headers:
- `X-Signature`: HMAC-SHA256 signature of the payload.
- `X-Timestamp`: Unix timestamp of the request.

Signature calculation:
```php
$payload = $timestamp . '.' . $rawBody;
$signature = hash_hmac('sha256', $payload, $secret);
```

### Expiration
Webhook requests older than 5 minutes will be rejected to prevent replay attacks.
