<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Events\EmailDeliveryFailed;
use App\Models\EmailEvent;
use App\Models\EmailSend;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Support\Settings\SettingsManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;

/**
 * Email Service
 *
 * Core email sending service with template rendering, idempotency, and suppression list.
 * Integrates with EmailGateway for actual delivery.
 */
class EmailService
{
    /**
     * Create a new Email Service instance.
     *
     * @param \App\Services\Email\EmailGatewayInterface $gateway
     * @param \App\Support\Settings\SettingsManager $settings
     */
    public function __construct(
        private readonly EmailGatewayInterface $gateway,
        private readonly SettingsManager $settings
    ) {
    }

    /**
     * Send email from template with full tracking and error handling.
     *
     * This is the main entry point for sending emails in the application.
     *
     * @param string $templateKey Template identifier (e.g., 'user-registered')
     * @param string $language Language code ('pl', 'en')
     * @param string $recipient Recipient email address
     * @param array $data Variables to render in template
     * @param array $metadata Additional data for tracking (user_id, appointment_id, etc.)
     * @return \App\Models\EmailSend The email send record
     * @throws \Exception If email is suppressed or template not found
     */
    public function sendFromTemplate(
        string $templateKey,
        string $language,
        string $recipient,
        array $data,
        array $metadata = []
    ): EmailSend {
        // Step 1: Check suppression list
        if (EmailSuppression::isSuppressed($recipient)) {
            Log::warning('Email blocked by suppression list', [
                'recipient' => $recipient,
                'template' => $templateKey,
            ]);

            throw new \Exception("Email address {$recipient} is suppressed and cannot receive emails.");
        }

        // Step 2: Fetch template from database
        $template = EmailTemplate::where('key', $templateKey)
            ->where('language', $language)
            ->where('active', true)
            ->first();

        // Step 3: Try fallback Blade view if template not found
        if (!$template) {
            $bladeViewName = "emails.{$templateKey}-{$language}";

            if (view()->exists($bladeViewName)) {
                Log::info('Using fallback Blade template', [
                    'template' => $templateKey,
                    'language' => $language,
                    'blade_view' => $bladeViewName,
                ]);

                return $this->sendFromBladeView($bladeViewName, $templateKey, $language, $recipient, $data, $metadata);
            }

            throw new \Exception("Email template '{$templateKey}' not found for language '{$language}'.");
        }

        // Step 4: Generate unique message key for idempotency
        $messageKey = $this->generateMessageKey($templateKey, $recipient, $metadata);

        // Step 5: Check for duplicate (idempotency check)
        $existingSend = EmailSend::where('message_key', $messageKey)->first();

        if ($existingSend) {
            Log::info('Duplicate email send detected, returning existing record', [
                'message_key' => $messageKey,
                'email_send_id' => $existingSend->id,
            ]);

            return $existingSend;
        }

        // Step 6: Render template
        $rendered = $this->renderTemplate($template, $data);

        // Step 7: Create EmailSend record (status='pending')
        $emailSend = EmailSend::create([
            'template_key' => $templateKey,
            'language' => $language,
            'recipient_email' => $recipient,
            'subject' => $rendered['subject'],
            'body_html' => $rendered['html'],
            'body_text' => $rendered['text'],
            'status' => 'pending',
            'metadata' => $metadata,
            'message_key' => $messageKey,
        ]);

        // Step 8: Try to send via EmailGateway
        try {
            $this->gateway->send(
                $recipient,
                $rendered['subject'],
                $rendered['html'],
                $rendered['text'],
                $metadata
            );

            // Success: mark as sent
            $emailSend->markAsSent();

            // Create 'sent' event
            EmailEvent::create([
                'email_send_id' => $emailSend->id,
                'type' => 'sent',
                'data' => [
                    'sent_at' => now()->toISOString(),
                    'gateway' => 'smtp',
                ],
            ]);

            Log::info('Email sent successfully', [
                'email_send_id' => $emailSend->id,
                'recipient' => $recipient,
                'template' => $templateKey,
            ]);
        } catch (\Exception $e) {
            // Failure: mark as failed
            $emailSend->markAsFailed($e->getMessage());

            // Create 'failed' event
            EmailEvent::create([
                'email_send_id' => $emailSend->id,
                'type' => 'failed',
                'data' => [
                    'error' => $e->getMessage(),
                    'failed_at' => now()->toISOString(),
                ],
            ]);

            Log::error('Email sending failed', [
                'email_send_id' => $emailSend->id,
                'recipient' => $recipient,
                'template' => $templateKey,
                'error' => $e->getMessage(),
            ]);

            // Dispatch EmailDeliveryFailed event
            event(new EmailDeliveryFailed($emailSend, $e->getMessage()));

            // Re-throw exception for queue retry
            throw $e;
        }

        return $emailSend;
    }

    /**
     * Render email template with Blade engine.
     *
     * @param \App\Models\EmailTemplate $template
     * @param array $data Variables to render
     * @return array{subject: string, html: string, text: string|null}
     */
    public function renderTemplate(EmailTemplate $template, array $data): array
    {
        try {
            // Render subject
            $subject = Blade::render($template->subject, $data);

            // Render HTML body
            $html = Blade::render($template->html_body, $data);

            // Render text body if exists
            $text = $template->text_body
                ? Blade::render($template->text_body, $data)
                : null;

            return [
                'subject' => $subject,
                'html' => $html,
                'text' => $text,
            ];
        } catch (\Exception $e) {
            Log::error('Template rendering failed', [
                'template_key' => $template->key,
                'language' => $template->language,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Failed to render template: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Send email from Blade view (fallback method).
     *
     * Used when template doesn't exist in database but Blade file exists.
     *
     * @param string $bladeViewName Blade view name (e.g., 'emails.user-registered-pl')
     * @param string $templateKey Template identifier
     * @param string $language Language code
     * @param string $recipient Recipient email address
     * @param array $data Variables to render
     * @param array $metadata Additional tracking data
     * @return \App\Models\EmailSend
     * @throws \Exception
     */
    private function sendFromBladeView(
        string $bladeViewName,
        string $templateKey,
        string $language,
        string $recipient,
        array $data,
        array $metadata = []
    ): EmailSend {
        // Render Blade view
        $html = view($bladeViewName, $data)->render();

        // Extract subject from data or use default
        $subject = $data['subject'] ?? "Email from " . config('app.name');

        // Generate message key
        $messageKey = $this->generateMessageKey($templateKey, $recipient, $metadata);

        // Check for duplicate
        $existingSend = EmailSend::where('message_key', $messageKey)->first();

        if ($existingSend) {
            return $existingSend;
        }

        // Create EmailSend record
        $emailSend = EmailSend::create([
            'template_key' => $templateKey,
            'language' => $language,
            'recipient_email' => $recipient,
            'subject' => $subject,
            'body_html' => $html,
            'body_text' => null,
            'status' => 'pending',
            'metadata' => $metadata,
            'message_key' => $messageKey,
        ]);

        // Try to send
        try {
            $this->gateway->send($recipient, $subject, $html, null, $metadata);

            $emailSend->markAsSent();

            EmailEvent::create([
                'email_send_id' => $emailSend->id,
                'type' => 'sent',
                'data' => ['sent_at' => now()->toISOString()],
            ]);
        } catch (\Exception $e) {
            $emailSend->markAsFailed($e->getMessage());

            EmailEvent::create([
                'email_send_id' => $emailSend->id,
                'type' => 'failed',
                'data' => ['error' => $e->getMessage()],
            ]);

            event(new EmailDeliveryFailed($emailSend, $e->getMessage()));

            throw $e;
        }

        return $emailSend;
    }

    /**
     * Generate unique message key for idempotency.
     *
     * Format: md5("{template_key}:{recipient}:{metadata_json}")
     *
     * @param string $templateKey
     * @param string $recipient
     * @param array $metadata
     * @return string
     */
    private function generateMessageKey(string $templateKey, string $recipient, array $metadata): string
    {
        $metadataString = json_encode($metadata, JSON_THROW_ON_ERROR);

        return md5("{$templateKey}:{$recipient}:{$metadataString}");
    }
}
