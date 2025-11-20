<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Migration Tracker Service
 *
 * Tracks UI/feature migrations in database (similar to Laravel database migrations).
 * Provides audit trail for refactorings, rollbacks, and architectural changes.
 *
 * @package App\Services
 */
class MigrationTrackerService
{
    /**
     * Record a migration execution.
     *
     * @param string $name Migration identifier (e.g., 'UI-MIGRATION-001')
     * @param string $type Migration type (ui_consolidation, feature_removal, etc.)
     * @param array $details Details about changes (files added/modified/deleted)
     * @return void
     */
    public function recordMigration(string $name, string $type, array $details): void
    {
        try {
            DB::table('ui_migrations')->updateOrInsert(
                ['name' => $name],
                [
                    'type' => $type,
                    'details' => json_encode($details),
                    'status' => 'completed',
                    'executed_at' => now(),
                    'updated_at' => now(),
                ]
            );

            Log::info("UI Migration recorded: {$name}", [
                'type' => $type,
                'details' => $details,
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to record UI migration: {$name}", [
                'error' => $e->getMessage(),
                'type' => $type,
            ]);
        }
    }

    /**
     * Record a migration rollback.
     *
     * @param string $name Migration identifier
     * @param string $reason Reason for rollback
     * @return void
     */
    public function recordRollback(string $name, string $reason): void
    {
        try {
            DB::table('ui_migrations')
                ->where('name', $name)
                ->update([
                    'status' => 'rolled_back',
                    'rollback_reason' => $reason,
                    'rolled_back_at' => now(),
                    'updated_at' => now(),
                ]);

            Log::warning("UI Migration rolled back: {$name}", [
                'reason' => $reason,
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to record UI migration rollback: {$name}", [
                'error' => $e->getMessage(),
                'reason' => $reason,
            ]);
        }
    }

    /**
     * Get all migrations.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllMigrations()
    {
        try {
            return DB::table('ui_migrations')
                ->orderBy('executed_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch UI migrations', [
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    /**
     * Get migration by name.
     *
     * @param string $name Migration identifier
     * @return object|null
     */
    public function getMigration(string $name)
    {
        try {
            return DB::table('ui_migrations')
                ->where('name', $name)
                ->first();
        } catch (\Exception $e) {
            Log::error("Failed to fetch UI migration: {$name}", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if migration exists.
     *
     * @param string $name Migration identifier
     * @return bool
     */
    public function exists(string $name): bool
    {
        try {
            return DB::table('ui_migrations')
                ->where('name', $name)
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get migration status.
     *
     * @param string $name Migration identifier
     * @return string|null 'pending', 'completed', 'rolled_back'
     */
    public function getStatus(string $name): ?string
    {
        try {
            $migration = DB::table('ui_migrations')
                ->where('name', $name)
                ->value('status');

            return $migration;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Mark migration as pending.
     *
     * @param string $name Migration identifier
     * @param string $type Migration type
     * @return void
     */
    public function markPending(string $name, string $type): void
    {
        try {
            DB::table('ui_migrations')->updateOrInsert(
                ['name' => $name],
                [
                    'type' => $type,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            Log::info("UI Migration marked as pending: {$name}", [
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to mark UI migration as pending: {$name}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
