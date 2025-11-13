# SMS System & Notifications

**Status:** ✅ Production Ready (November 2025)

Complete SMS notification system with SMSAPI.pl integration, queue-based delivery, multi-language support (PL/EN), and Filament admin panel for management.

## Quick Overview

- **14 SMS Templates** (7 types × 2 languages: Polish + English)
- **Queue-Based Delivery** (Redis + Laravel Horizon)
- **SMSAPI.pl Integration** with webhook callbacks
- **4 Filament Resources** (templates, sends, events, suppressions)
- **Idempotency** via unique message_key (prevents duplicates)
- **SMS Suppression List** (opt-out/invalid number handling)
- **160-character limit** (GSM-7) or **70-character limit** (Unicode for Polish)
- **Test Mode** (sandbox) for development

## Quick Links

- **[Architecture](./architecture.md)** - Services, Models, Webhook, Flow
- **[Templates](./templates.md)** - Template management, variables, seeding
- **[SMSAPI Integration](./smsapi-integration.md)** - Configuration, webhook, callbacks

## Features

### Multi-Language Support
All templates available in Polish (PL) and English (EN). User's `preferred_language` field determines which template is sent.

### Template Types

| Template Key              | Purpose                          | Typical Length |
|---------------------------|----------------------------------|----------------|
| appointment-created       | Booking confirmation             | ~130 chars     |
| appointment-confirmed     | Admin confirmation               | ~120 chars     |
| appointment-rescheduled   | Date/time change notification    | ~110 chars     |
| appointment-cancelled     | Cancellation notice              | ~140 chars     |
| appointment-reminder-24h  | 24-hour reminder                 | ~140 chars     |
| appointment-reminder-2h   | 2-hour reminder                  | ~120 chars     |
| appointment-followup      | Post-service feedback request    | ~130 chars     |

### Queue Architecture
- **Backend:** Redis
- **Monitor:** Laravel Horizon (https://paradocks.local:8444/horizon)
- **Queues:** `default` queue (same as emails)
- **Workers:** Auto-scaling (shared with email queue)
- **Retries:** 3 attempts with exponential backoff
- **Timeout:** 60 seconds per job

## Quick Start

### Send SMS from Code

```php
use App\Services\Sms\SmsService;

$smsService = app(SmsService::class);

$smsSend = $smsService->sendFromTemplate(
    templateKey: 'appointment-reminder-24h',
    language: 'pl',
    phoneNumber: '+48501234567',
    data: [
        'service_name' => 'Detailing zewnętrzny',
        'appointment_date' => '2025-11-15',
        'appointment_time' => '14:00',
        'location_address' => 'ul. Marszałkowska 1, Warszawa',
        'app_name' => 'Paradocks',
    ],
    metadata: ['appointment_id' => 123]
);

if ($smsSend->status === 'sent') {
    echo "SMS sent successfully! Message ID: {$smsSend->smsapi_message_id}";
}
```

### Send Test SMS

```php
use App\Services\Sms\SmsService;

$smsService = app(SmsService::class);

// Sends generic test SMS in specified language
$smsSend = $smsService->sendTestSms(
    phoneNumber: '+48501234567',
    language: 'pl' // or 'en'
);
```

**OR** use the admin panel:
1. Go to **System Settings** → **SMS Tab**
2. Configure SMSAPI token and settings
3. Click **"Test SMS Connection"** button
4. SMS will be sent to your user's phone_e164

## Configuration

### Environment Variables

```bash
# .env
SMSAPI_TOKEN=your-smsapi-token-here
SMSAPI_SERVICE=pl  # or 'com' for international
SMSAPI_SENDER_NAME=Paradocks  # Max 11 characters
SMSAPI_TEST_MODE=false  # Set to true for sandbox mode
```

### System Settings (Filament Admin)

Go to **System Settings** → **SMS Tab**:

**SMSAPI Configuration:**
- SMS Enabled (toggle)
- API Token (secure, revealable)
- Service (pl/com)
- Sender Name (max 11 chars, alphanumeric)
- Test Mode (sandbox for development)

**SMS Notification Settings:**
- Booking Confirmation SMS
- Admin Confirmation SMS
- 24-Hour Reminder SMS
- 2-Hour Reminder SMS
- Follow-up SMS

## Architecture Overview

### Models

**SmsTemplate** (`app/Models/SmsTemplate.php`)
- Stores SMS templates with {{placeholders}}
- Fields: `key`, `language`, `message_body`, `variables`, `max_length`, `active`
- Supports Blade rendering: `{{customer_name}}` → actual value
- Methods: `render($data)`, `exceedsMaxLength($message)`, `truncateMessage($message)`

**SmsSend** (`app/Models/SmsSend.php`)
- Records of sent SMS messages
- Fields: `phone_number`, `message_body`, `status`, `smsapi_message_id`, `message_key`, `metadata`
- Statuses: `pending`, `sent`, `failed`, `delivered`
- Idempotent via `message_key` (prevents duplicate sends)

**SmsEvent** (`app/Models/SmsEvent.php`)
- Audit trail of SMS lifecycle events
- Fields: `sms_send_id`, `event_type`, `smsapi_status`, `error_message`, `raw_response`
- Event types: `sent`, `failed`, `delivered`, `undelivered`
- Populated by webhook callbacks from SMSAPI.pl

**SmsSuppression** (`app/Models/SmsSuppression.php`)
- Opt-out and invalid number blacklist
- Fields: `phone_number`, `reason`, `suppressed_at`
- Reasons: `opt_out`, `invalid_number`, `manual`
- Checked before sending (skips suppressed numbers)

### Services

**SmsService** (`app/Services/Sms/SmsService.php`)
- Main service for sending SMS
- Methods:
  - `sendFromTemplate()` - Send SMS using template
  - `sendTestSms()` - Send test SMS
  - `renderTemplate()` - Render Blade template with data
- Handles idempotency, suppression list, error logging

**SmsApiGateway** (`app/Services/Sms/SmsApiGateway.php`)
- Integration with SMSAPI.pl HTTP API
- Methods:
  - `send()` - Send SMS via SMSAPI.pl
  - `checkCredits()` - Get account balance
  - `getMessageStatus()` - Check delivery status
- Handles authentication, error responses, test mode

### Webhook Controller

**SmsApiWebhookController** (`app/Http/Controllers/Api/SmsApiWebhookController.php`)
- Receives delivery status callbacks from SMSAPI.pl
- Updates `sms_sends` status and creates `sms_events`
- Route: `POST /api/webhooks/smsapi`
- No authentication required (SMSAPI.pl doesn't support signatures)

## Database Schema

```sql
-- SMS Templates (14 records: 7 types × 2 languages)
sms_templates (
    id, key, language, message_body, variables, max_length, active, created_at, updated_at
)

-- SMS Sends (history of sent messages)
sms_sends (
    id, phone_number, message_body, status, template_key, template_language,
    smsapi_message_id, message_key, message_parts, cost, metadata,
    sent_at, delivered_at, failed_at, created_at, updated_at
)

-- SMS Events (audit trail)
sms_events (
    id, sms_send_id, event_type, smsapi_status, error_message, raw_response,
    created_at, updated_at
)

-- SMS Suppressions (blacklist)
sms_suppressions (
    id, phone_number, reason, suppressed_at, created_at, updated_at
)
```

## Filament Resources

### SmsTemplateResource
**Location:** `app/Filament/Resources/SmsTemplateResource.php`

**Features:**
- CRUD for SMS templates
- Filters: Active/Inactive, Language (PL/EN), Template Type
- Actions:
  - **Test Send** - Send test SMS from template
  - Bulk Activate/Deactivate
- Columns: Key, Language, Message Preview (truncated), Max Length, Active status
- Form validation: Max 160 characters, required variables

### SmsSendResource
**Location:** `app/Filament/Resources/SmsSendResource.php`

**Features:**
- View-only (no create/edit - created programmatically)
- Filters: Status, Template Key, Date Range
- Search: Phone number, message body
- Columns: Phone, Message (truncated), Status badge, Template, Sent At
- Actions:
  - **View Details** - Full message, metadata, events
  - **Resend** - Retry failed SMS (creates new send)

### SmsEventResource
**Location:** `app/Filament/Resources/SmsEventResource.php`

**Features:**
- Audit trail of SMS events
- Filters: Event Type, Date Range
- Relation: Links to SmsSend
- Columns: Event Type, SMSAPI Status, Error Message, Created At
- Expandable: Shows raw SMSAPI response JSON

### SmsSuppressionResource
**Location:** `app/Filament/Resources/SmsSuppressionResource.php`

**Features:**
- CRUD for suppression list
- Filters: Reason (opt_out, invalid_number, manual)
- Search: Phone number
- Actions:
  - **Add to Blacklist** - Manual suppression
  - **Remove** - Un-suppress number
  - Bulk Delete

## Testing

### Manual Testing (Admin Panel)

1. **Configure SMSAPI:**
   - Go to **System Settings** → **SMS Tab**
   - Add your SMSAPI token
   - Set service to `pl`
   - Set sender name (e.g., "Paradocks")
   - Enable Test Mode if using sandbox

2. **Add Phone Number to User:**
   - Go to your user profile
   - Add `phone_e164` field (e.g., "+48501234567")

3. **Test Connection:**
   - Click **"Test SMS Connection"** button
   - Check your phone for test SMS

4. **Send Template SMS:**
   - Go to **SMS Templates**
   - Select a template
   - Click **"Test Send"** action
   - Enter phone number
   - Check SMS delivery

### Automated Testing (PHPUnit)

```bash
# Run all SMS tests
php artisan test --filter=Sms

# Test SMS service
php artisan test tests/Unit/Services/SmsServiceTest.php

# Test SMSAPI gateway
php artisan test tests/Unit/Services/SmsApiGatewayTest.php
```

## Troubleshooting

### SMS Not Sending

**Check:**
1. **SMSAPI Token Valid?**
   - Go to System Settings → SMS → Check API Token
   - Run `php artisan tinker`: `app(\App\Services\Sms\SmsApiGateway::class)->checkCredits()`
   - Should return account balance, not error

2. **Phone Number Format:**
   - Must be E.164 format: `+48501234567` (with country code)
   - No spaces, dashes, or parentheses
   - Laravel validates with `phone:E164` rule

3. **Test Mode Enabled?**
   - Check System Settings → SMS → Test Mode
   - If true, SMS are sent to sandbox (not real phones)
   - Disable for production

4. **Template Exists?**
   - Check **SMS Templates** resource
   - Should have 14 templates (7 types × 2 languages)
   - If missing, run: `php artisan db:seed --class=SmsTemplateSeeder`

5. **Number Suppressed?**
   - Check **SMS Suppressions** resource
   - If phone number is blacklisted, SMS will be skipped
   - Remove from suppression list to send

### SMS Sent But Not Delivered

**Check:**
1. **Delivery Status:**
   - Go to **SMS Sends** → View Details
   - Check status: `sent` → `delivered` (should update via webhook)
   - If stuck at `sent`, webhook may not be configured

2. **Webhook Configuration:**
   - SMSAPI.pl dashboard → Webhooks
   - Add webhook URL: `https://your-domain.com/api/webhooks/smsapi`
   - Events: `delivered`, `undelivered`
   - Test webhook from SMSAPI dashboard

3. **SMS Events:**
   - Go to **SMS Events** resource
   - Look for `delivered` or `undelivered` events
   - Check `error_message` for delivery failures

### Test Mode Not Working

**Solution:**
- SMSAPI.pl sandbox requires separate token
- Get sandbox token from SMSAPI.pl dashboard
- Use sandbox token when Test Mode is enabled
- Sandbox doesn't actually send SMS (simulates sending)

## Performance & Costs

### SMSAPI.pl Pricing (2025)

- **Poland (pl service):** ~0.10 PLN per SMS (160 chars)
- **Multi-part SMS:** 2× cost for 161-306 chars, 3× for 307-459 chars
- **International:** Higher rates (varies by country)

### Optimization Tips

1. **Keep Messages Under 160 Characters (GSM-7)**
   - Polish characters (ą, ć, ę, ł, ń, ó, ś, ź, ż) use Unicode → 70 char limit
   - Remove Polish diacritics for longer messages (160 chars)

2. **Use Template Variables Efficiently**
   - Shorter variable values = more room for message
   - Example: "Wizyta jutro 14:00" instead of "Wizyta jutro o godzinie 14:00"

3. **Disable Unnecessary SMS Types**
   - System Settings → SMS → Disable notification types you don't need
   - Example: Disable "Booking Confirmation" if email is sent anyway

4. **Batch Sending**
   - For mass SMS (e.g., marketing), use SMSAPI.pl batch API
   - Not implemented yet (future enhancement)

## Security

### SMSAPI Token Security

- **Storage:** Token stored in `settings` table (encrypted at rest)
- **Access:** Only Super Admins can view/edit token in Filament
- **Revocation:** Revoke token from SMSAPI.pl dashboard if compromised

### Webhook Security

- **No Authentication:** SMSAPI.pl doesn't support webhook signatures
- **IP Whitelist:** Limit webhook route to SMSAPI.pl IP ranges (optional)
- **Idempotency:** Webhook can be called multiple times safely (no duplicates)

### Phone Number Privacy

- **Hashing:** Phone numbers stored in plain text (required for sending)
- **Access Control:** Only admins with `view sms sends` permission can see numbers
- **Suppression List:** Users can opt-out (add to suppression list)

## Future Enhancements

- [ ] **Batch SMS Sending** - Send to multiple numbers at once
- [ ] **SMS Scheduling** - Schedule SMS for future delivery
- [ ] **SMS Templates in Filament** - Edit templates via UI (currently seed only)
- [ ] **SMS Analytics** - Delivery rates, costs, popular templates
- [ ] **Two-Way SMS** - Receive SMS replies (requires dedicated number)
- [ ] **International Support** - Multi-country sender names, Unicode handling
- [ ] **A/B Testing** - Test different message variations

## Related Documentation

- **[Email System](../email-system/README.md)** - Email notifications (similar architecture)
- **[Settings System](../settings-system/README.md)** - System settings management
- **[Booking System](../booking-system/README.md)** - Appointment creation (triggers SMS)

## Support

**Issues:** https://github.com/paradocks/app/issues
**SMSAPI.pl Docs:** https://www.smsapi.pl/docs
**SMSAPI.pl Support:** https://www.smsapi.pl/kontakt

---

**Last Updated:** November 2025
**Maintainer:** Development Team
