# SMS System Architecture

## Overview

The SMS System follows a clean, event-driven architecture with clear separation of concerns:

```
User Action → Service Layer → Gateway → SMSAPI.pl → Webhook → Event Logging
```

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        Application Layer                         │
├─────────────────────────────────────────────────────────────────┤
│  Controllers / Livewire / Jobs / Event Listeners                │
│  (Trigger SMS sending)                                           │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                         SmsService                               │
├─────────────────────────────────────────────────────────────────┤
│  • sendFromTemplate($key, $language, $phoneNumber, $data)       │
│  • sendTestSms($phoneNumber, $language)                         │
│  • renderTemplate($template, $data)                             │
│  • Check suppression list                                       │
│  • Generate message_key for idempotency                         │
│  • Create SmsSend record                                        │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                       SmsApiGateway                              │
├─────────────────────────────────────────────────────────────────┤
│  • send($phoneNumber, $messageBody, $from)                      │
│  • checkCredits()                                               │
│  • getMessageStatus($messageId)                                 │
│  • HTTP Client: Guzzle                                          │
│  • API: https://api.smsapi.pl/sms.do                           │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                         SMSAPI.pl                                │
├─────────────────────────────────────────────────────────────────┤
│  • Delivers SMS to recipient's phone                            │
│  • Sends delivery status callbacks via webhook                  │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                   SmsApiWebhookController                        │
├─────────────────────────────────────────────────────────────────┤
│  POST /api/webhooks/smsapi                                      │
│  • Receives delivery status updates                             │
│  • Updates SmsSend status                                       │
│  • Creates SmsEvent for audit trail                             │
└─────────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. Models

#### SmsTemplate
**Location:** `app/Models/SmsTemplate.php`

Stores SMS message templates with Blade-style variable placeholders.

**Fields:**
- `id` - Primary key
- `key` - Template identifier (e.g., 'appointment-reminder-24h')
- `language` - Language code ('pl', 'en')
- `message_body` - SMS message template with `{{placeholders}}`
- `variables` - Array of available variables (JSON)
- `max_length` - Maximum SMS length (160 for GSM, 70 for Unicode)
- `active` - Template is active (boolean)
- `created_at`, `updated_at` - Timestamps

**Methods:**
- `render(array $data): string` - Render template with Blade
- `exceedsMaxLength(string $message): bool` - Check if message is too long
- `truncateMessage(string $message, string $suffix = '...'): string` - Truncate to max length

**Scopes:**
- `active()` - Only active templates
- `forKey(string $key)` - Filter by template key
- `forLanguage(string $language)` - Filter by language

**Example:**
```php
$template = SmsTemplate::forKey('appointment-reminder-24h')
    ->forLanguage('pl')
    ->firstOrFail();

$rendered = $template->render([
    'service_name' => 'Detailing zewnętrzny',
    'appointment_date' => '2025-11-15',
    'appointment_time' => '14:00',
    'location_address' => 'ul. Marszałkowska 1, Warszawa',
    'app_name' => 'Paradocks',
]);
```

---

#### SmsSend
**Location:** `app/Models/SmsSend.php`

Records of sent SMS messages.

**Fields:**
- `id` - Primary key
- `phone_number` - Recipient phone (E.164 format)
- `message_body` - Actual SMS content sent
- `status` - Message status: `pending`, `sent`, `failed`, `delivered`
- `template_key` - Template used (nullable)
- `template_language` - Language used (nullable)
- `smsapi_message_id` - SMSAPI.pl message ID
- `message_key` - Idempotency key (unique)
- `message_parts` - Number of SMS parts (1-3)
- `cost` - Cost in PLN (decimal)
- `metadata` - Additional data (JSON, e.g., appointment_id)
- `sent_at` - When SMS was sent
- `delivered_at` - When SMS was delivered (from webhook)
- `failed_at` - When SMS failed (from webhook)
- `created_at`, `updated_at` - Timestamps

**Relationships:**
- `hasMany(SmsEvent)` - Lifecycle events

**Scopes:**
- `status(string $status)` - Filter by status
- `sentToday()` - Sent today
- `failed()` - Failed SMS

**Idempotency:**
The `message_key` field ensures no duplicate SMS are sent. It's generated from:
```php
$messageKey = md5($phoneNumber . $templateKey . $language . $appointmentId . $timestamp);
```

**Example:**
```php
$smsSend = SmsSend::create([
    'phone_number' => '+48501234567',
    'message_body' => 'Przypomnienie! Jutro masz wizytę...',
    'status' => 'pending',
    'template_key' => 'appointment-reminder-24h',
    'template_language' => 'pl',
    'message_key' => md5('unique-key'),
    'metadata' => ['appointment_id' => 123],
]);
```

---

#### SmsEvent
**Location:** `app/Models/SmsEvent.php`

Audit trail of SMS lifecycle events (sent, failed, delivered).

**Fields:**
- `id` - Primary key
- `sms_send_id` - Foreign key to `sms_sends`
- `event_type` - Event type: `sent`, `failed`, `delivered`, `undelivered`
- `smsapi_status` - SMSAPI.pl status code (e.g., '401', '404')
- `error_message` - Error description (nullable)
- `raw_response` - Full SMSAPI.pl response (JSON)
- `created_at`, `updated_at` - Timestamps

**Relationships:**
- `belongsTo(SmsSend)` - Parent SMS send

**Example:**
```php
SmsEvent::create([
    'sms_send_id' => 123,
    'event_type' => 'delivered',
    'smsapi_status' => '401', // SMSAPI.pl status: delivered
    'raw_response' => json_encode($webhookPayload),
]);
```

---

#### SmsSuppression
**Location:** `app/Models/SmsSuppression.php`

Blacklist of phone numbers that should not receive SMS.

**Fields:**
- `id` - Primary key
- `phone_number` - Phone number (E.164 format, unique)
- `reason` - Reason: `opt_out`, `invalid_number`, `manual`
- `suppressed_at` - When suppressed
- `created_at`, `updated_at` - Timestamps

**Scopes:**
- `reason(string $reason)` - Filter by reason

**Example:**
```php
// Check if number is suppressed
if (SmsSuppression::where('phone_number', '+48501234567')->exists()) {
    // Skip sending
}

// Add to suppression list
SmsSuppression::create([
    'phone_number' => '+48501234567',
    'reason' => 'opt_out',
    'suppressed_at' => now(),
]);
```

---

### 2. Services

#### SmsService
**Location:** `app/Services/Sms/SmsService.php`

Main service for sending SMS. Handles template rendering, suppression checks, idempotency, and error handling.

**Constructor Dependencies:**
- `SmsApiGateway` - For sending SMS
- `SettingsManager` - For SMS settings (enabled, sender name, etc.)

**Methods:**

**`sendFromTemplate()`**
```php
public function sendFromTemplate(
    string $templateKey,
    string $language,
    string $phoneNumber,
    array $data,
    ?array $metadata = null,
    ?int $appointmentId = null
): SmsSend
```

Sends SMS using a template.

**Steps:**
1. Check if SMS is enabled (`sms.enabled` setting)
2. Check if phone number is suppressed
3. Find template by key + language
4. Render template with data (Blade)
5. Generate idempotent `message_key`
6. Check if SMS already sent (duplicate prevention)
7. Create `SmsSend` record (status: `pending`)
8. Call `SmsApiGateway->send()`
9. Update status to `sent` or `failed`
10. Create `SmsEvent` for audit

**`sendTestSms()`**
```php
public function sendTestSms(
    string $phoneNumber,
    string $language = 'pl'
): SmsSend
```

Sends a generic test SMS (no template). Used for testing SMSAPI connection.

**`renderTemplate()`**
```php
protected function renderTemplate(SmsTemplate $template, array $data): string
```

Renders template using Blade's `compileString()`. Falls back to simple string replacement if Blade fails.

---

#### SmsApiGateway
**Location:** `app/Services/Sms/SmsApiGateway.php`

Integration with SMSAPI.pl HTTP API.

**Constructor Dependencies:**
- `SettingsManager` - For SMSAPI token, service, sender name
- `Guzzle\Client` - HTTP client

**Methods:**

**`send()`**
```php
public function send(
    string $phoneNumber,
    string $messageBody,
    ?string $from = null
): array
```

Sends SMS via SMSAPI.pl.

**Request:**
```http
POST https://api.smsapi.pl/sms.do
Authorization: Bearer {token}
Content-Type: application/json

{
  "to": "+48501234567",
  "message": "Your SMS text here",
  "from": "Paradocks",
  "test": false
}
```

**Response (Success):**
```json
{
  "count": 1,
  "list": [
    {
      "id": "sms-123456",
      "points": 0.1,
      "number": "+48501234567",
      "date_sent": 1635789012,
      "submitted_number": "48501234567",
      "status": "SENT",
      "error": null
    }
  ]
}
```

**Response (Error):**
```json
{
  "error": "Invalid phone number",
  "code": 13
}
```

**`checkCredits()`**
```php
public function checkCredits(): float
```

Gets account balance from SMSAPI.pl.

**`getMessageStatus()`**
```php
public function getMessageStatus(string $messageId): array
```

Checks delivery status of a specific message.

---

### 3. Webhook Controller

#### SmsApiWebhookController
**Location:** `app/Http/Controllers/Api/SmsApiWebhookController.php`

Receives delivery status callbacks from SMSAPI.pl.

**Route:**
```php
// routes/api.php
Route::post('/webhooks/smsapi', [SmsApiWebhookController::class, 'handle'])
    ->name('webhooks.smsapi');
```

**Webhook Payload (from SMSAPI.pl):**
```json
{
  "id": "sms-123456",
  "status": "DELIVERED", // or "UNDELIVERED"
  "error": null,
  "date_sent": 1635789012,
  "date_delivered": 1635789120,
  "number": "+48501234567"
}
```

**Handler Logic:**
1. Find `SmsSend` by `smsapi_message_id`
2. Update status to `delivered` or `failed`
3. Set `delivered_at` or `failed_at` timestamp
4. Create `SmsEvent` with webhook data
5. Return `200 OK` to SMSAPI.pl

**Configuration in SMSAPI.pl:**
- Go to SMSAPI.pl dashboard → Webhooks
- Add URL: `https://your-domain.com/api/webhooks/smsapi`
- Select events: `DELIVERED`, `UNDELIVERED`
- Save

---

## Data Flow

### Sending SMS

```
1. User Action (e.g., appointment created)
   ↓
2. Event Listener / Job calls SmsService->sendFromTemplate()
   ↓
3. SmsService:
   - Loads template from database
   - Renders template with Blade
   - Checks suppression list
   - Generates message_key
   - Creates SmsSend (status: pending)
   ↓
4. SmsApiGateway->send()
   - Makes HTTP POST to SMSAPI.pl
   - Returns message_id or error
   ↓
5. SmsService updates SmsSend:
   - Status: sent (or failed)
   - smsapi_message_id: from SMSAPI.pl
   - sent_at: now()
   ↓
6. SmsEvent created (type: sent or failed)
```

### Receiving Delivery Status

```
1. SMSAPI.pl delivers SMS to recipient's phone
   ↓
2. SMSAPI.pl sends webhook callback to:
   POST /api/webhooks/smsapi
   ↓
3. SmsApiWebhookController->handle()
   - Finds SmsSend by smsapi_message_id
   - Updates status to delivered/failed
   - Sets delivered_at/failed_at timestamp
   - Creates SmsEvent (type: delivered/undelivered)
   ↓
4. Admin can view delivery status in Filament:
   SMS Sends → View Details → Events tab
```

---

## Queue Integration

SMS sending is **asynchronous** via Laravel Queue (Redis + Horizon).

### Queue Configuration

**config/horizon.php:**
```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'], // SMS uses default queue
            'balance' => 'auto',
            'maxProcesses' => 3,
            'tries' => 3,
            'timeout' => 60,
        ],
    ],
],
```

### Queued Jobs

**SendSmsJob** (future enhancement - not yet implemented)
- Queued job for sending SMS
- Payload: template_key, language, phone_number, data, metadata
- Retries: 3 attempts with exponential backoff
- Timeout: 60 seconds

**Current Implementation:**
SMS are sent **synchronously** within event listeners (e.g., `AppointmentCreated` event listener calls `SmsService->sendFromTemplate()` directly). This is acceptable for low volume but should be queued for production.

**TODO:** Create `SendSmsJob` and dispatch from event listeners:
```php
// Event Listener
SendSmsJob::dispatch(
    templateKey: 'appointment-reminder-24h',
    language: 'pl',
    phoneNumber: '+48501234567',
    data: $data,
    metadata: ['appointment_id' => $appointment->id],
)->onQueue('default');
```

---

## Security Considerations

### SMSAPI Token Storage
- Stored in `settings` table (encrypted at rest by Laravel)
- Only accessible via `SettingsManager` service
- Only Super Admins can view/edit in Filament

### Webhook Security
- **No authentication:** SMSAPI.pl doesn't support webhook signatures
- **Mitigation:** Limit webhook route to SMSAPI.pl IP ranges (optional)
- **Idempotency:** Webhook can be called multiple times safely

### Phone Number Validation
- All phone numbers validated as E.164 format (`+48501234567`)
- Laravel validation rule: `phone:E164`
- Invalid numbers rejected before sending

### Rate Limiting (future)
- SMSAPI.pl has rate limits (depends on plan)
- Implement rate limiting in `SmsApiGateway` to prevent hitting limits
- Use Laravel's `RateLimiter` facade

---

## Testing Strategy

### Unit Tests
- **SmsTemplateTest:** Test rendering, truncation, max length
- **SmsSendTest:** Test status transitions, idempotency
- **SmsServiceTest:** Mock SmsApiGateway, test sendFromTemplate logic
- **SmsApiGatewayTest:** Mock HTTP responses, test error handling

### Feature Tests
- **SendSmsFeatureTest:** End-to-end test (template → send → event)
- **WebhookHandlerTest:** Test webhook payload processing
- **SuppressionListTest:** Test opt-out flow

### Manual Testing
- Use **Test Mode** in SMSAPI.pl (sandbox)
- Click **"Test SMS Connection"** in System Settings
- Send test SMS from **SMS Templates** → **Test Send** action

---

## Monitoring & Observability

### Laravel Horizon Dashboard
**URL:** https://paradocks.local:8444/horizon

- **Jobs:** View queued/failed SMS jobs
- **Metrics:** Throughput, wait time, job count
- **Failed Jobs:** Retry or delete failed SMS sends

### Filament Resources
- **SMS Sends:** View all sent SMS, filter by status
- **SMS Events:** Audit trail of delivery events
- **SMS Suppressions:** Blacklist management

### Logs
**Location:** `storage/logs/laravel.log`

```
[2025-11-12 10:30:45] local.INFO: SMS sent {"sms_send_id": 123, "phone": "+48501234567", "template": "appointment-reminder-24h"}
[2025-11-12 10:30:46] local.ERROR: SMS failed {"error": "Invalid phone number", "phone": "+48999999999"}
```

---

## Performance Optimization

### Database Indexes
```sql
-- sms_sends table
INDEX idx_phone_number (phone_number)
INDEX idx_status (status)
INDEX idx_message_key (message_key) -- For idempotency checks
INDEX idx_smsapi_message_id (smsapi_message_id) -- For webhook lookups

-- sms_events table
INDEX idx_sms_send_id (sms_send_id)
INDEX idx_event_type (event_type)

-- sms_suppressions table
UNIQUE INDEX idx_phone_number (phone_number) -- Prevent duplicates
```

### Caching
- **Templates:** Cache rendered templates (future enhancement)
- **Suppression List:** Cache in Redis for faster lookups
- **Settings:** SettingsManager uses Laravel cache

### Batch Sending (future)
- SMSAPI.pl supports batch API (send up to 1000 SMS at once)
- Implement `SmsService->sendBatch()` for mass SMS campaigns

---

## Related Documentation

- **[SMS Templates](./templates.md)** - Template management, variables, seeding
- **[SMSAPI Integration](./smsapi-integration.md)** - Configuration, webhook, callbacks
- **[README](./README.md)** - Overview and quick start

---

**Last Updated:** November 2025
