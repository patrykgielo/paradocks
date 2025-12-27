---
paths:
  - "app/Notifications/**"
---

# Notification Rules

## Required Interfaces for Email Notifications

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class AppointmentConfirmation extends Notification implements ShouldQueue, ShouldBeUnique
{
    use Queueable;
}
```

## Uniqueness (CRITICAL - prevent duplicates)

```php
/**
 * Unique identifier for deduplication
 */
public function uniqueId(): string
{
    return $this->appointment->id . '_confirmation';
}

/**
 * How long the uniqueness lock should last
 */
public function uniqueFor(): int
{
    return 3600; // 1 hour
}
```

## Queue Configuration

```php
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment
    ) {
        // Route to specific queue
        $this->onQueue('emails');
    }
}
```

## Constructor Pattern

```php
public function __construct(
    public Appointment $appointment,
    protected ?string $recipientType = 'customer'
) {
    $this->onQueue('emails');
}
```

## EmailService Integration

```php
public function toMail(object $notifiable): MailMessage
{
    return app(EmailService::class)->buildNotification(
        template: 'appointment-confirmation',
        data: [
            'appointment' => $this->appointment,
            'customer' => $notifiable,
        ]
    );
}
```

## Multi-channel Support

```php
public function via(object $notifiable): array
{
    $channels = ['mail'];

    // Add SMS if consent given
    if ($notifiable->sms_consent_given_at) {
        $channels[] = 'sms';
    }

    return $channels;
}
```

## Naming Convention

- `{Entity}{Action}` - AppointmentConfirmation, BookingCancelled
- Customer variant: `AppointmentConfirmationCustomer`
- Admin variant: `AppointmentConfirmationAdmin`

## DocBlock Documentation

```php
/**
 * Notification sent to customer after successful booking
 *
 * Channels: email, sms (if consent)
 * Queue: emails
 * Unique for: 1 hour per appointment
 */
class AppointmentConfirmation extends Notification
```

## Strict Types

```php
<?php

declare(strict_types=1);

namespace App\Notifications;
```

## Testing Notifications

```php
// W testach
Notification::fake();

// Wykonaj akcję
$this->service->createAppointment($data);

// Asercje
Notification::assertSentTo(
    $user,
    AppointmentConfirmation::class
);
```

## Istniejące Notifications (reference)

- `AppointmentConfirmation` - potwierdzenie rezerwacji
- `AppointmentCancelled` - anulowanie
- `AppointmentReminder` - przypomnienie
- `PasswordSetup` - setup hasła dla admin-created users
