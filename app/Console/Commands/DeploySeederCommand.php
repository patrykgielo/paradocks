<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Deploy Seeder Command
 *
 * Smart seeder execution for production deployments with automatic detection
 * of first vs subsequent deployment context.
 *
 * Features:
 * - Detects deployment context via Settings table count
 * - First deployment: Runs all 6 production-safe seeders
 * - Subsequent: Runs only EmailTemplateSeeder + SmsTemplateSeeder
 * - Dry-run mode for preview without execution
 * - Force-all flag to override detection
 * - Critical error handling (exit code 1 on failure)
 *
 * @see /var/www/projects/paradocks/app/docs/deployment/runbooks/ci-cd-deployment.md
 */
class DeploySeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'deploy:seed
                            {--dry-run : Preview seeders without executing}
                            {--force-all : Run all seeders regardless of detection}';

    /**
     * The console command description.
     */
    protected $description = 'Smart seeder execution for production deployments (first vs subsequent detection)';

    /**
     * Seeders to run on FIRST deployment (empty database).
     *
     * These are all production-safe seeders that use updateOrCreate()
     * for idempotency. Run in order of dependencies.
     */
    private const FIRST_DEPLOYMENT_SEEDERS = [
        'SettingSeeder',           // ~1-2s (40 settings) - MUST BE FIRST (detection key)
        'RolePermissionSeeder',    // ~500ms (4 roles, 50 permissions)
        'VehicleTypeSeeder',       // ~200ms (5 types)
        'ServiceSeeder',           // ~500ms (8 services)
        'EmailTemplateSeeder',     // ~3-5s (30 templates)
        'SmsTemplateSeeder',       // ~1-2s (14 templates)
    ];

    /**
     * Seeders to run on SUBSEQUENT deployments (data exists).
     *
     * Only template seeders that change frequently with new features.
     * Settings/Roles/Services are one-time setup, don't need re-seeding.
     */
    private const SUBSEQUENT_DEPLOYMENT_SEEDERS = [
        'EmailTemplateSeeder',     // ~3-5s (updates templates for new features)
        'SmsTemplateSeeder',       // ~1-2s (updates templates for new features)
    ];

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 = success, 1 = failure)
     */
    public function handle(): int
    {
        $this->info('🌱 Deploy Seeder - Smart Seeder Execution');
        $this->newLine();

        // Step 1: Detect deployment context (first vs subsequent)
        $isFirstDeployment = $this->detectDeploymentContext();
        if ($isFirstDeployment === null) {
            return 1; // Detection failed
        }

        // Step 2: Determine which seeders to run
        $seeders = $this->determineSeeders($isFirstDeployment);

        // Step 3: Display execution plan
        $this->displayExecutionPlan($isFirstDeployment, $seeders);
        $this->newLine();

        // Step 4: Dry-run mode (exit early without execution)
        if ($this->option('dry-run')) {
            $this->warn('Dry-run mode - no seeders executed');

            return 0;
        }

        // Step 5: Execute seeders sequentially
        return $this->executeSeeders($seeders);
    }

    /**
     * Detect deployment context by checking Settings table.
     *
     * Logic: Settings::count() == 0 → first deployment
     *        Settings::count() > 0  → subsequent deployment
     *
     * Why Settings? SettingSeeder runs FIRST in DatabaseSeeder, so if
     * Settings table is empty, database is brand new (first deployment).
     *
     * @return bool|null True = first, False = subsequent, Null = detection failed
     */
    private function detectDeploymentContext(): ?bool
    {
        $this->info('🔍 Detecting deployment context...');

        try {
            $settingsCount = Setting::count();

            if ($settingsCount === 0) {
                $this->info('   ✓ First deployment detected (Settings table empty)');

                return true;
            } else {
                $this->info("   ✓ Subsequent deployment detected ({$settingsCount} settings exist)");

                return false;
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Detection failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Determine which seeders to run based on deployment context.
     *
     * @param  bool  $isFirstDeployment  Deployment context from detection
     * @return array List of seeder class names to execute
     */
    private function determineSeeders(bool $isFirstDeployment): array
    {
        // Force-all flag: override detection, run all seeders
        if ($this->option('force-all')) {
            $this->warn('   ⚠️  --force-all flag: Running all seeders');

            return self::FIRST_DEPLOYMENT_SEEDERS;
        }

        // Return appropriate seeders based on context
        return $isFirstDeployment
            ? self::FIRST_DEPLOYMENT_SEEDERS
            : self::SUBSEQUENT_DEPLOYMENT_SEEDERS;
    }

    /**
     * Display execution plan (what will be seeded).
     *
     * @param  bool  $isFirstDeployment  Deployment context
     * @param  array  $seeders  List of seeders to execute
     */
    private function displayExecutionPlan(bool $isFirstDeployment, array $seeders): void
    {
        $this->info('📋 Execution Plan:');
        $this->info('   Context: '.($isFirstDeployment ? 'First Deployment' : 'Subsequent Deployment'));
        $this->info('   Seeders to run: '.count($seeders));
        $this->newLine();

        foreach ($seeders as $index => $seeder) {
            $this->info('   '.($index + 1).'. '.$seeder);
        }
    }

    /**
     * Execute seeders sequentially with timing and error handling.
     *
     * Strategy: Stop on first failure (no cascading errors).
     * Each seeder is independent, but stopping early prevents
     * confusion if one fails.
     *
     * @param  array  $seeders  List of seeder class names
     * @return int Exit code (0 = all success, 1 = any failure)
     */
    private function executeSeeders(array $seeders): int
    {
        $this->newLine();
        $this->info('🚀 Executing seeders...');

        $successCount = 0;
        $failureCount = 0;
        $totalTime = 0;

        foreach ($seeders as $seeder) {
            $this->info("   Running: {$seeder}...");

            $startTime = microtime(true);
            $exitCode = Artisan::call('db:seed', [
                '--class' => $seeder,
                '--force' => true, // Bypass Laravel's production confirmation
            ]);
            $duration = round((microtime(true) - $startTime) * 1000);
            $totalTime += $duration;

            if ($exitCode === 0) {
                $this->info("   ✓ {$seeder} completed ({$duration}ms)");
                $successCount++;
            } else {
                $this->error("   ✗ {$seeder} failed (exit code: {$exitCode})");
                $failureCount++;
                break; // Stop on first failure
            }
        }

        $this->newLine();

        // Summary output
        if ($failureCount > 0) {
            $this->error('❌ Seeder execution failed');
            $this->error("   Executed: {$successCount}/".count($seeders));
            $this->error("   Failed: {$failureCount}");

            return 1;
        }

        $this->info('✅ All seeders completed successfully');
        $this->info("   Executed: {$successCount}/".count($seeders));
        $this->info("   Total time: {$totalTime}ms");

        return 0;
    }
}
