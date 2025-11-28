<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MaintenanceType;
use App\Models\MaintenanceEvent;
use App\Models\User;
use App\Support\Settings\SettingsManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * MaintenanceService
 *
 * Manages application maintenance mode with Redis-based state storage.
 * Singleton service registered in AppServiceProvider.
 */
class MaintenanceService
{
    /**
     * Redis cache keys
     */
    private const CACHE_KEY_MODE = 'maintenance:mode';
    private const CACHE_KEY_CONFIG = 'maintenance:config';
    private const CACHE_KEY_ENABLED_AT = 'maintenance:enabled_at';
    private const CACHE_KEY_SECRET_TOKEN = 'maintenance:secret_token';

    public function __construct(
        private SettingsManager $settingsManager
    ) {}

    /**
     * Check if maintenance mode is currently active.
     */
    public function isActive(): bool
    {
        return Cache::store('redis')->has(self::CACHE_KEY_MODE);
    }

    /**
     * Get current maintenance mode type.
     */
    public function getType(): ?MaintenanceType
    {
        $mode = Cache::store('redis')->get(self::CACHE_KEY_MODE);

        if (!$mode) {
            return null;
        }

        return MaintenanceType::from($mode);
    }

    /**
     * Get maintenance configuration.
     */
    public function getConfig(): array
    {
        return Cache::store('redis')->get(self::CACHE_KEY_CONFIG, []);
    }

    /**
     * Check if user can bypass maintenance mode.
     */
    public function canBypass(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $type = $this->getType();

        if (!$type) {
            return true; // No maintenance active
        }

        // Pre-launch mode: NO bypass allowed
        if ($type === MaintenanceType::PRELAUNCH) {
            return false;
        }

        // Check role-based bypass
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            Log::info('Maintenance bypass granted', [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $type->value,
                'method' => 'role-based',
            ]);
            return true;
        }

        return false;
    }

    /**
     * Check if secret token is valid for bypass.
     */
    public function checkSecretToken(string $token): bool
    {
        $storedToken = Cache::store('redis')->get(self::CACHE_KEY_SECRET_TOKEN);

        if (!$storedToken) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    /**
     * Enable maintenance mode.
     */
    public function enable(
        MaintenanceType $type,
        ?User $user = null,
        array $config = []
    ): void {
        // Generate secret token for bypass (except pre-launch)
        $secretToken = null;
        if ($type !== MaintenanceType::PRELAUNCH) {
            $secretToken = $this->generateSecretToken();
        }

        // Store in Redis (no TTL - manually disabled)
        Cache::store('redis')->put(self::CACHE_KEY_MODE, $type->value);
        Cache::store('redis')->put(self::CACHE_KEY_CONFIG, $config);
        Cache::store('redis')->put(self::CACHE_KEY_ENABLED_AT, now()->toIso8601String());

        if ($secretToken) {
            Cache::store('redis')->put(self::CACHE_KEY_SECRET_TOKEN, $secretToken);
        }

        // Create file trigger for pre-launch (Nginx check)
        if ($type === MaintenanceType::PRELAUNCH) {
            $this->createPrelaunchFile();
        }

        // Log event to database
        $this->logEvent(
            action: 'enabled',
            type: $type,
            user: $user,
            metadata: array_merge($config, [
                'secret_token' => $secretToken,
                'enabled_via' => 'MaintenanceService',
            ])
        );

        Log::info('Maintenance mode enabled', [
            'type' => $type->value,
            'user_id' => $user?->id,
            'config' => $config,
            'secret_token' => $secretToken ? 'generated' : 'none',
        ]);
    }

    /**
     * Disable maintenance mode.
     */
    public function disable(?User $user = null): void
    {
        $type = $this->getType();

        if (!$type) {
            Log::warning('Attempted to disable maintenance mode when not active');
            return;
        }

        // Remove from Redis
        Cache::store('redis')->forget(self::CACHE_KEY_MODE);
        Cache::store('redis')->forget(self::CACHE_KEY_CONFIG);
        Cache::store('redis')->forget(self::CACHE_KEY_ENABLED_AT);
        Cache::store('redis')->forget(self::CACHE_KEY_SECRET_TOKEN);

        // Remove file trigger (if exists)
        $this->removePrelaunchFile();

        // Log event
        $this->logEvent(
            action: 'disabled',
            type: $type,
            user: $user,
            metadata: [
                'disabled_via' => 'MaintenanceService',
            ]
        );

        Log::info('Maintenance mode disabled', [
            'type' => $type->value,
            'user_id' => $user?->id,
        ]);
    }

    /**
     * Get current status information.
     */
    public function getStatus(): array
    {
        $isActive = $this->isActive();
        $type = $this->getType();

        return [
            'active' => $isActive,
            'type' => $type?->value,
            'type_label' => $type?->label(),
            'can_bypass' => $type?->canBypass(),
            'retry_after' => $type?->retryAfter(),
            'enabled_at' => Cache::store('redis')->get(self::CACHE_KEY_ENABLED_AT),
            'config' => $this->getConfig(),
        ];
    }

    /**
     * Get secret bypass token (for sharing with admins).
     */
    public function getSecretToken(): ?string
    {
        return Cache::store('redis')->get(self::CACHE_KEY_SECRET_TOKEN);
    }

    /**
     * Regenerate secret token.
     */
    public function regenerateSecretToken(?User $user = null): string
    {
        $newToken = $this->generateSecretToken();

        Cache::store('redis')->put(self::CACHE_KEY_SECRET_TOKEN, $newToken);

        Log::info('Secret token regenerated', [
            'user_id' => $user?->id,
        ]);

        return $newToken;
    }

    /**
     * Log maintenance event to database.
     */
    private function logEvent(
        string $action,
        MaintenanceType $type,
        ?User $user = null,
        array $metadata = []
    ): void {
        try {
            MaintenanceEvent::create([
                'type' => $type,
                'action' => $action,
                'user_id' => $user?->id,
                'ip_address' => request()->ip(),
                'message' => $metadata['message'] ?? null,
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log maintenance event', [
                'error' => $e->getMessage(),
                'action' => $action,
                'type' => $type->value,
            ]);
        }
    }

    /**
     * Generate random secret token.
     */
    private function generateSecretToken(): string
    {
        return 'paradocks-' . Str::random(32);
    }

    /**
     * Create file trigger for pre-launch mode (Nginx check).
     */
    private function createPrelaunchFile(): void
    {
        $filePath = storage_path('framework/maintenance.mode');

        try {
            file_put_contents($filePath, 'prelaunch');
        } catch (\Exception $e) {
            Log::error('Failed to create pre-launch file', [
                'path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove file trigger for pre-launch mode.
     */
    private function removePrelaunchFile(): void
    {
        $filePath = storage_path('framework/maintenance.mode');

        if (file_exists($filePath)) {
            try {
                unlink($filePath);
            } catch (\Exception $e) {
                Log::error('Failed to remove pre-launch file', [
                    'path' => $filePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
