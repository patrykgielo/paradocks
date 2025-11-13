<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Models\SmsEvent;
use App\Models\SmsSend;
use App\Models\SmsSuppression;
use App\Models\SmsTemplate;
use App\Support\Settings\SettingsManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;

/**
 * SMS Service
 *
 * Core SMS sending service with template rendering, idempotency, and suppression list.
 * Integrates with SmsGateway for actual delivery.
 */
class SmsService
{
    /**
     * Create a new SMS Service instance.
     *
     * @param \App\Services\Sms\SmsGatewayInterface $gateway
     * @param \App\Support\Settings\SettingsManager $settings
     */
    public function __construct(
        private readonly SmsGatewayInterface $gateway,
        private readonly SettingsManager $settings
    ) {
    }

    /**
     * Send SMS from template with full tracking and error handling.
     *
     * This is the main entry point for sending SMS in the application.
     *
     * @param string $templateKey Template identifier (e.g., 'appointment-reminder-24h')
     * @param string $language Language code ('pl', 'en')
     * @param string $recipient Recipient phone number in international format (+48...)
     * @param array $data Variables to render in template
     * @param array $metadata Additional data for tracking (user_id, appointment_id, etc.)
     * @return \App\Models\SmsSend The SMS send record
     * @throws \Exception If SMS is suppressed or template not found
     */
    public function sendFromTemplate(
        string $templateKey,
        string $language,
        string $recipient,
        array $data,
        array $metadata = []
    ): SmsSend {
        // Step 1: Check if SMS is enabled globally
        $smsSettings = $this->settings->group('sms');
        if (!($smsSettings['enabled'] ?? true)) {
            Log::warning('SMS sending is disabled globally', [
                'recipient' => $recipient,
                'template' => $templateKey,
            ]);

            throw new \Exception('SMS sending is currently disabled in system settings.');
        }

        // Step 2: Check suppression list
        if (SmsSuppression::isSuppressed($recipient)) {
            Log::warning('SMS blocked by suppression list', [
                'recipient' => $recipient,
                'template' => $templateKey,
            ]);

            throw new \Exception("Phone number {$recipient} is suppressed and cannot receive SMS.");
        }

        // Step 3: Fetch template from database
        $template = SmsTemplate::where('key', $templateKey)
            ->where('language', $language)
            ->where('active', true)
            ->first();

        if (!$template) {
            throw new \Exception("SMS template '{$templateKey}' not found for language '{$language}'.");
        }

        // Step 4: Generate unique message key for idempotency
        $messageKey = $this->generateMessageKey($templateKey, $recipient, $metadata);

        // Step 5: Check for duplicate (idempotency check)
        $existingSend = SmsSend::where('message_key', $messageKey)->first();

        if ($existingSend) {
            Log::info('Duplicate SMS send detected, returning existing record', [
                'message_key' => $messageKey,
                'sms_send_id' => $existingSend->id,
            ]);

            return $existingSend;
        }

        // Step 6: Render template
        $messageBody = $this->renderTemplate($template, $data);

        // Step 7: Validate message length
        $lengthInfo = $this->gateway->calculateMessageLength($messageBody);
        if ($lengthInfo['length'] > $template->max_length) {
            Log::warning('SMS message exceeds max length', [
                'template' => $templateKey,
                'length' => $lengthInfo['length'],
                'max_length' => $template->max_length,
            ]);

            // Truncate message if too long
            $messageBody = mb_substr($messageBody, 0, $template->max_length);
            $lengthInfo = $this->gateway->calculateMessageLength($messageBody);
        }

        // Step 8: Create SmsSend record (status='pending')
        $smsSend = SmsSend::create([
            'template_key' => $templateKey,
            'language' => $language,
            'phone_to' => $recipient,
            'message_body' => $messageBody,
            'status' => 'pending',
            'metadata' => $metadata,
            'message_key' => $messageKey,
            'message_length' => $lengthInfo['length'],
            'message_parts' => $lengthInfo['parts'],
        ]);

        // Step 9: Try to send via SmsGateway
        try {
            $response = $this->gateway->send(
                $recipient,
                $messageBody,
                $metadata
            );

            // Success: mark as sent and store SMS ID
            $smsSend->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sms_id' => $response['sms_id'],
            ]);

            // Create 'sent' event
            SmsEvent::create([
                'sms_send_id' => $smsSend->id,
                'event_type' => 'sent',
                'occurred_at' => now(),
                'event_data' => [
                    'sent_at' => now()->toISOString(),
                    'sms_id' => $response['sms_id'],
                    'gateway' => 'smsapi',
                ],
            ]);

            Log::info('SMS sent successfully', [
                'sms_send_id' => $smsSend->id,
                'recipient' => $recipient,
                'template' => $templateKey,
                'sms_id' => $response['sms_id'],
            ]);
        } catch (\Throwable $e) {
            // Failure: mark as failed
            $smsSend->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Create 'failed' event
            SmsEvent::create([
                'sms_send_id' => $smsSend->id,
                'event_type' => 'failed',
                'occurred_at' => now(),
                'event_data' => [
                    'error' => $e->getMessage(),
                    'failed_at' => now()->toISOString(),
                ],
            ]);

            Log::error('SMS sending failed', [
                'sms_send_id' => $smsSend->id,
                'recipient' => $recipient,
                'template' => $templateKey,
                'error' => $e->getMessage(),
            ]);

            // Re-throw exception for queue retry
            throw $e;
        }

        return $smsSend;
    }

    /**
     * Render SMS template with Blade engine.
     *
     * Converts {{variable}} syntax to {{ $variable }} and renders with Blade.
     *
     * @param \App\Models\SmsTemplate $template
     * @param array $data Variables to render
     * @return string Rendered message body
     */
    public function renderTemplate(SmsTemplate $template, array $data): string
    {
        try {
            // Use SmsTemplate's render method which handles {{variable}} → {{ $variable }} conversion
            return $template->render($data);
        } catch (\Throwable $e) {
            Log::error('SMS template rendering failed', [
                'template_key' => $template->key,
                'language' => $template->language,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Failed to render SMS template: {$e->getMessage()}", 0, $e);
        }
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

    /**
     * Send test SMS to verify configuration.
     *
     * @param string $recipient Phone number to send test SMS
     * @param string $language Language code ('pl', 'en')
     * @return \App\Models\SmsSend
     * @throws \Exception
     */
    public function sendTestSms(string $recipient, string $language = 'pl'): SmsSend
    {
        $testMessage = $language === 'pl'
            ? 'To jest testowa wiadomość SMS z systemu Paradocks.'
            : 'This is a test SMS message from Paradocks system.';

        // Create SmsSend record directly (no template)
        $smsSend = SmsSend::create([
            'template_key' => 'test-message',
            'language' => $language,
            'phone_to' => $recipient,
            'message_body' => $testMessage,
            'status' => 'pending',
            'metadata' => ['type' => 'test'],
            'message_key' => md5('test:' . $recipient . ':' . now()->timestamp),
        ]);

        try {
            $response = $this->gateway->send($recipient, $testMessage, ['test_mode' => false]);

            $smsSend->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sms_id' => $response['sms_id'],
                'message_length' => $response['message_length'],
                'message_parts' => $response['message_parts'],
            ]);

            SmsEvent::create([
                'sms_send_id' => $smsSend->id,
                'event_type' => 'sent',
                'occurred_at' => now(),
                'event_data' => ['sms_id' => $response['sms_id']],
            ]);

            Log::info('Test SMS sent successfully', [
                'sms_send_id' => $smsSend->id,
                'recipient' => $recipient,
            ]);
        } catch (\Throwable $e) {
            $smsSend->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $smsSend;
    }
}
