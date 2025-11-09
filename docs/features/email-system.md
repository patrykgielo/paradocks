# Email System & Notifications - Feature Documentation

**Version:** 1.0
**Implemented:** November 2025
**Status:** Production Ready ✅

## Quick Overview

Complete transactional email system for Paradocks with:
- ✅ Queue-based asynchronous delivery (Redis + Horizon)
- ✅ Multi-language support (Polish and English)
- ✅18 email templates (9 types × 2 languages)
- ✅ Gmail SMTP with App Password authentication
- ✅ Filament admin panel for email management
- ✅ Automated reminders, follow-ups, and admin digests
- ✅ GDPR-compliant 90-day retention
- ✅ Idempotent email delivery (no duplicates)

## Business Requirements Implemented

### 1. User Registration Flow
- ✅ Welcome email after account creation
- ✅ Email verification link (if enabled)
- ✅ Multi-language support (user's preferred language)

### 2. Appointment Booking Flow
- ✅ Confirmation email after booking (status: pending)
- ✅ Notification to customer when admin confirms booking
- ✅ Reschedule notification when date/time changes
- ✅ Cancellation confirmation email

### 3. Appointment Reminders
- ✅ 24-hour reminder (sent automatically)
- ✅ 2-hour reminder (sent automatically)
- ✅ Only sent to confirmed appointments
- ✅ Respects email suppression list

### 4. Post-Service Follow-Up
- ✅ Follow-up email 24h after completed appointment
- ✅ Feedback/review request
- ✅ Booking link for next service

### 5. Admin Notifications
- ✅ Daily digest with statistics (sent at 8:00 AM)
- ✅ New appointments summary
- ✅ Today's appointments list
- ✅ Email delivery statistics

## Technical Architecture

### Core Components

```
┌─────────────────────────────────────────────────────────────┐
│                     Email System Flow                        │
└─────────────────────────────────────────────────────────────┘

User Action (Create/Update Appointment)
    │
    ├──→ Appointment Model Event (AppointmentCreated)
    │        │
    │        └──→ Event Listener (AppServiceProvider)
    │                 │
    │                 └──→ Notification (AppointmentCreatedNotification)
    │                          │
    │                          └──→ Queue (emails)
    │                                   │
    │                                   └──→ EmailService::sendFromTemplate()
    │                                            │
    │                                            ├──→ Check EmailSuppression
    │                                            ├──→ Load EmailTemplate
    │                                            ├──→ Render with Blade
    │                                            ├──→ Check Duplicate (message_key)
    │                                            ├──→ Create EmailSend record
    │                                            ├──→ EmailGateway::send() [SMTP]
    │                                            └──→ Create EmailEvent (sent/failed)
    │
Scheduler (Cron Every Minute)
    │
    ├──→ Hourly: SendReminderEmailsJob
    │        └──→ Find appointments 24h/2h ahead → Send reminders
    │
    ├──→ Hourly: SendFollowUpEmailsJob
    │        └──→ Find completed appointments from 24h ago → Send follow-up
    │
    ├──→ Daily 8:00 AM: SendAdminDigestJob
    │        └──→ Collect statistics → Send to all admins
    │
    └──→ Daily 2:00 AM: CleanupOldEmailLogsJob
             └──→ Delete email_sends/events older than 90 days (GDPR)
```

### Database Schema

**email_templates** (18 rows)
- Stores email templates with Blade syntax
- Unique constraint: `(key, language)`
- Columns: key, language, subject, html_body, text_body, blade_path, variables, active

**email_sends** (logs all sent emails)
- Tracks delivery status and metadata
- Unique constraint: `message_key` (prevents duplicates)
- Columns: template_key, recipient_email, subject, body_html, status, sent_at, message_key, error_message

**email_events** (tracks delivery lifecycle)
- Records email events (sent, delivered, bounced, complained, opened, clicked)
- Foreign key: `email_send_id` (cascade delete)
- Columns: email_send_id, event_type, event_data, occurred_at

**email_suppressions** (bounce/unsubscribe list)
- Prevents sending to suppressed emails
- Unique constraint: `email`
- Columns: email, reason (bounced|complained|unsubscribed|manual), suppressed_at

**appointments** (extended with reminder tracking)
- New fields: `sent_24h_reminder`, `sent_2h_reminder`, `sent_followup` (boolean)
- Prevents duplicate reminders

### Email Templates

| Template Key              | Purpose                          | Variables                                      |
|---------------------------|----------------------------------|------------------------------------------------|
| user-registered           | Welcome email                    | user_name, user_email, app_name                |
| password-reset            | Password reset link              | user_name, reset_link, app_name                |
| appointment-created       | Booking confirmation             | customer_name, appointment_date, service_name  |
| appointment-rescheduled   | Date/time change notification    | customer_name, old_date, new_date, service_name|
| appointment-cancelled     | Cancellation confirmation        | customer_name, appointment_date, service_name  |
| appointment-reminder-24h  | 24-hour reminder                 | customer_name, appointment_date, location      |
| appointment-reminder-2h   | 2-hour reminder                  | customer_name, appointment_time, location      |
| appointment-followup      | Post-service feedback request    | customer_name, service_name, review_url        |
| admin-daily-digest        | Daily statistics for admins      | admin_name, new_appointments, email_stats      |

All templates exist in **PL** and **EN** (18 total).

## Configuration

### Gmail SMTP Setup (App Password Required)

**Step 1: Enable 2-Step Verification**
1. Go to https://myaccount.google.com/security
2. Click "2-Step Verification" → Turn On
3. Follow Google's setup wizard

**Step 2: Generate App Password**
1. Go to https://myaccount.google.com/apppasswords
2. Select app: Mail, Device: Other (Laravel)
3. Click "Generate"
4. Copy 16-character password (e.g., `abcd efgh ijkl mnop`)
5. Remove spaces → `abcdefghijklmnop`

**Step 3: Update Configuration**

`.env` file:
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Paradocks"
```

Database (via Filament admin):
1. Navigate to Admin Panel → Settings → Email tab
2. Fill in SMTP credentials
3. Click "Test Email Connection"
4. Verify test email received

### Queue Configuration

**Docker Services** (already configured):
```yaml
redis:       # Queue backend (port 6379)
queue:       # Queue worker (foreground, 3 max processes)
horizon:     # Queue dashboard (https://paradocks.local:8444/horizon)
scheduler:   # Cron scheduler (runs schedule:run every minute)
```

**Queues:**
- `emails` (high priority) - User-facing emails
- `reminders` (medium priority) - Automated reminders
- `default` (low priority) - Cleanup jobs

## Admin Panel Usage

### Email Templates (manage email templates)
**Path:** Admin → Email → Email Templates

**Actions:**
- **Create:** Add new template (requires unique key + language combo)
- **Edit:** Modify subject, body, variables
- **Preview:** View rendered template with sample data
- **Test Send:** Send test email to any address
- **Delete:** Remove template (with confirmation)

**Filters:**
- Language (PL/EN)
- Template key (dropdown)
- Active status (toggle)

### Email Sends (view email logs)
**Path:** Admin → Email → Email Sends

**Actions:**
- **View:** Full email HTML preview in iframe + metadata
- **Resend:** Create new EmailSend and dispatch to queue
- **Export to CSV:** Bulk export selected emails

**Filters:**
- Status (sent, failed, bounced, pending)
- Template key (dropdown)
- Date range (from → until)

**Status Badges:**
- 🟢 Sent - Delivered successfully
- 🔴 Failed - SMTP error occurred
- 🟡 Bounced - Email bounced (added to suppression list)
- ⚪ Pending - Queued, not yet sent

### Email Events (view delivery events)
**Path:** Admin → Email → Email Events

**Actions:**
- **View Email:** Redirect to EmailSendResource
- **Add to Suppression:** Manually suppress email (only for bounced/complained events)

**Event Types:**
- 📤 Sent - Email sent to SMTP server
- ✅ Delivered - Confirmed delivery
- ❌ Bounced - Hard/soft bounce
- ⚠️ Complained - Spam complaint
- 👁️ Opened - Email opened (if tracking enabled)
- 🔗 Clicked - Link clicked (if tracking enabled)

### Email Suppressions (manage suppression list)
**Path:** Admin → Email → Email Suppressions

**Actions:**
- **Create:** Manually suppress email address
- **Edit:** Change suppression reason
- **Delete:** Remove from suppression list (re-enables sending)

**Reasons:**
- **Bounced:** Hard bounce (invalid address)
- **Complained:** Spam complaint
- **Unsubscribed:** User opted out
- **Manual:** Admin decision

## Developer Usage

### Sending Email from Code

```php
use App\Services\Email\EmailService;

$emailService = app(EmailService::class);

$emailSend = $emailService->sendFromTemplate(
    templateKey: 'appointment-created',
    language: 'pl',
    recipient: 'customer@example.com',
    data: [
        'customer_name' => 'Jan Kowalski',
        'appointment_date' => '2025-11-15',
        'appointment_time' => '14:00',
        'service_name' => 'Detailing zewnętrzny',
        'location_address' => 'ul. Marszałkowska 1, Warszawa',
        'app_name' => config('app.name'),
    ],
    metadata: [
        'appointment_id' => 123,
    ]
);

// Check status
if ($emailSend->status === 'sent') {
    echo "Email sent at: {$emailSend->sent_at}";
} else {
    echo "Failed: {$emailSend->error_message}";
}
```

### Dispatching Events

```php
use App\Events\AppointmentCreated;
use App\Models\Appointment;

// Automatically dispatched by Appointment model on creation
$appointment = Appointment::create([/* ... */]);

// Manual dispatch (if needed)
event(new AppointmentCreated($appointment));
```

### Testing Email Flow

```bash
# Test complete flow (user + appointment + jobs)
docker compose exec app php artisan email:test-flow --all

# Test specific user
docker compose exec app php artisan email:test-flow --user=1

# Test specific appointment
docker compose exec app php artisan email:test-flow --appointment=1

# Send test email via Tinker
docker compose exec app php artisan tinker
>>> $user = User::first();
>>> event(new \App\Events\UserRegistered($user));
>>> exit

# Process queue immediately (for testing)
docker compose exec app php artisan queue:work --once --queue=emails
```

## Monitoring & Troubleshooting

### Check Queue Status

**Horizon Dashboard:**
https://paradocks.local:8444/horizon

Metrics:
- Jobs per minute
- Failed jobs
- Recent jobs
- Queue wait times

**CLI Commands:**
```bash
# Check queue status
docker compose exec app php artisan queue:monitor redis:emails,redis:reminders

# List scheduled tasks
docker compose exec app php artisan schedule:list

# View failed jobs
docker compose exec app php artisan horizon:failed

# Retry failed jobs
docker compose exec app php artisan queue:retry all
```

### Check Email Logs

**Mailpit (Development):**
http://localhost:8025

**Database:**
```sql
-- Recent emails
SELECT id, template_key, recipient_email, status, sent_at
FROM email_sends
ORDER BY created_at DESC
LIMIT 10;

-- Failed emails
SELECT * FROM email_sends WHERE status = 'failed';

-- Suppressed emails
SELECT * FROM email_suppressions;
```

**Laravel Logs:**
```bash
docker compose exec app tail -f storage/logs/laravel.log | grep Email
```

### Common Issues

**1. Emails not sending (queue not processing)**

Check queue worker:
```bash
docker compose ps queue horizon
docker compose restart queue horizon
```

**2. Gmail authentication failed (535-5.7.8)**

Fix: Use App Password, not regular password
```bash
# Update .env
MAIL_PASSWORD=your-app-password

# Restart services
docker compose restart queue horizon
```

**3. Duplicate emails being sent**

Check idempotency:
```sql
SELECT message_key, COUNT(*) FROM email_sends GROUP BY message_key HAVING COUNT(*) > 1;
```

**4. Template rendering errors**

Check template variables:
```sql
SELECT `key`, language, `variables` FROM email_templates WHERE `key` = 'appointment-created';
```

Ensure all variables in template match data passed to `sendFromTemplate()`.

## Security & Best Practices

**✅ Do:**
- Use App Passwords for Gmail (never regular password)
- Store credentials in `.env` (not in code)
- Test emails in Mailpit before production
- Monitor failed jobs in Horizon
- Keep suppression list updated

**❌ Don't:**
- Commit `.env` to Git
- Use untrusted user input in templates without escaping
- Delete suppression list (GDPR/unsubscribe compliance)
- Send emails synchronously (always use queues)
- Exceed Gmail rate limits (500/day free, 2,000/day paid)

## Future Enhancements (Not Yet Implemented)

- [ ] Open/click tracking (webhook integration)
- [ ] A/B testing for email templates
- [ ] Email analytics dashboard (open rate, click rate)
- [ ] SendGrid/Mailgun integration (for high volume)
- [ ] Email preview in multiple clients (Litmus/Email on Acid)
- [ ] Attachment support (invoices, receipts)
- [ ] Inline images (logo, vehicle photos)
- [ ] Rich text editor for templates (TinyMCE/CKEditor)

## Testing Checklist

- [x] Gmail SMTP connection successful
- [x] Test email received in inbox (not spam)
- [x] User registration email sent
- [x] Appointment created email sent
- [x] Appointment rescheduled email sent
- [x] Appointment cancelled email sent
- [x] 24-hour reminder job finds appointments
- [x] 2-hour reminder job finds appointments
- [x] Follow-up job finds completed appointments
- [x] Admin digest sent to all admins
- [x] Cleanup job deletes old logs
- [x] Horizon dashboard accessible
- [x] Filament email resources accessible
- [x] Suppression list prevents sending
- [x] Idempotency prevents duplicates

## Support & Resources

- **CLAUDE.md:** Full implementation guide with code examples
- **Laravel Mail Docs:** https://laravel.com/docs/mail
- **Laravel Queues:** https://laravel.com/docs/queues
- **Laravel Horizon:** https://laravel.com/docs/horizon
- **Gmail SMTP:** https://support.google.com/mail/answer/7126229
- **App Passwords:** https://support.google.com/accounts/answer/185833

---

**Implemented by:** Claude Code (Anthropic)
**Date:** November 2025
**Project:** Paradocks - Mobile Car Detailing Booking System
