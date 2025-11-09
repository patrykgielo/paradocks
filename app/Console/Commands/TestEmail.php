<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Services\Email\EmailService;
use Illuminate\Console\Command;

/**
 * Test Email Command
 *
 * Tests the email system by sending test emails from all available templates.
 * Useful for verifying SMTP configuration and template rendering.
 */
class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test
                            {--to= : Recipient email address}
                            {--template= : Specific template key to test (optional)}
                            {--language=pl : Language code (pl or en)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending system with all or specific templates';

    /**
     * Execute the console command.
     */
    public function handle(EmailService $emailService): int
    {
        $to = $this->option('to');
        $templateKey = $this->option('template');
        $language = $this->option('language') ?? 'pl';

        // Validate recipient email
        if (!$to) {
            $this->error('❌ Recipient email is required. Use --to=email@example.com');
            return self::FAILURE;
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error("❌ Invalid email address: {$to}");
            return self::FAILURE;
        }

        $this->info("📧 Testing email system...");
        $this->info("   Recipient: {$to}");
        $this->info("   Language: {$language}");
        $this->newLine();

        // Test specific template or all templates
        if ($templateKey) {
            return $this->testSingleTemplate($emailService, $templateKey, $language, $to);
        }

        return $this->testAllTemplates($emailService, $language, $to);
    }

    /**
     * Test a single template.
     *
     * @param \App\Services\Email\EmailService $emailService
     * @param string $templateKey
     * @param string $language
     * @param string $to
     * @return int
     */
    private function testSingleTemplate(
        EmailService $emailService,
        string $templateKey,
        string $language,
        string $to
    ): int {
        $this->info("Testing template: {$templateKey} ({$language})");

        try {
            $data = $this->getTestData($templateKey);

            $emailSend = $emailService->sendFromTemplate(
                $templateKey,
                $language,
                $to,
                $data,
                ['test' => true, 'command' => 'email:test']
            );

            if ($emailSend->isSent()) {
                $this->info("✅ Template '{$templateKey}' sent successfully!");
                $this->info("   Email Send ID: {$emailSend->id}");
                $this->info("   Subject: {$emailSend->subject}");
                return self::SUCCESS;
            } else {
                $this->error("❌ Template '{$templateKey}' failed to send.");
                $this->error("   Error: {$emailSend->error_message}");
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Test all available templates.
     *
     * @param \App\Services\Email\EmailService $emailService
     * @param string $language
     * @param string $to
     * @return int
     */
    private function testAllTemplates(
        EmailService $emailService,
        string $language,
        string $to
    ): int {
        $templates = EmailTemplate::where('language', $language)
            ->where('active', true)
            ->get();

        if ($templates->isEmpty()) {
            $this->warn("⚠️  No active templates found for language: {$language}");
            return self::FAILURE;
        }

        $this->info("Found {$templates->count()} active templates for language '{$language}'");
        $this->newLine();

        $successCount = 0;
        $failureCount = 0;

        foreach ($templates as $template) {
            $this->info("Testing: {$template->key}");

            try {
                $data = $this->getTestData($template->key);

                $emailSend = $emailService->sendFromTemplate(
                    $template->key,
                    $language,
                    $to,
                    $data,
                    ['test' => true, 'command' => 'email:test']
                );

                if ($emailSend->isSent()) {
                    $this->info("  ✅ Sent successfully (ID: {$emailSend->id})");
                    $successCount++;
                } else {
                    $this->error("  ❌ Failed: {$emailSend->error_message}");
                    $failureCount++;
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Error: {$e->getMessage()}");
                $failureCount++;
            }

            $this->newLine();
        }

        // Summary
        $this->info("📊 Test Results:");
        $this->info("   ✅ Successful: {$successCount}");
        $this->info("   ❌ Failed: {$failureCount}");
        $this->info("   📧 Total: " . ($successCount + $failureCount));

        return $failureCount === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Get test data for a specific template.
     *
     * @param string $templateKey
     * @return array
     */
    private function getTestData(string $templateKey): array
    {
        // Common data for all templates
        $baseData = [
            'app_name' => config('app.name', 'Paradocks'),
            'user_name' => 'Test User',
            'customer_name' => 'Test Customer',
        ];

        // Template-specific data
        $specificData = match ($templateKey) {
            'user-registered' => [
                'user_email' => $this->option('to'),
            ],
            'password-reset' => [
                'reset_url' => url('/password/reset/test-token'),
                'token' => 'test-token-123',
            ],
            'appointment-created', 'appointment-rescheduled', 'appointment-cancelled' => [
                'service_name' => 'Premium Car Detailing',
                'appointment_date' => now()->addDays(3)->format('Y-m-d'),
                'appointment_time' => '14:00',
                'location_address' => 'ul. Testowa 123, Warszawa',
                'old_date' => now()->addDays(2)->format('Y-m-d H:i'),
                'new_date' => now()->addDays(3)->format('Y-m-d H:i'),
                'who_changed' => 'staff',
                'reason' => 'Test cancellation reason',
            ],
            'appointment-reminder-24h', 'appointment-reminder-2h' => [
                'service_name' => 'Premium Car Detailing',
                'appointment_date' => now()->addDay()->format('Y-m-d'),
                'appointment_time' => '14:00',
            ],
            'appointment-followup' => [
                'service_name' => 'Premium Car Detailing',
                'review_url' => url('/reviews/create?test=1'),
            ],
            default => [],
        };

        return array_merge($baseData, $specificData);
    }
}
