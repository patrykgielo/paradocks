<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\MaintenanceType;
use App\Services\MaintenanceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckMaintenanceMode Middleware
 *
 * Handles maintenance mode logic for the application.
 * Checks Redis state and determines if request should be blocked.
 *
 * Bypass logic:
 * - DEPLOYMENT/SCHEDULED/EMERGENCY: Admins can bypass via role or secret token
 * - PRELAUNCH: NO bypass allowed (returns 503 to everyone)
 *
 * Secret Token Bypass:
 * - Pass token via query parameter: ?maintenance_token=paradocks-xxxxx
 * - Token stored in session for subsequent requests
 */
class CheckMaintenanceMode
{
    public function __construct(
        private MaintenanceService $maintenanceService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if maintenance mode is not active
        if (! $this->maintenanceService->isActive()) {
            return $next($request);
        }

        $type = $this->maintenanceService->getType();

        if (! $type) {
            return $next($request);
        }

        // Check for secret token bypass (query parameter)
        if ($request->has('maintenance_token')) {
            $token = $request->query('maintenance_token');
            if ($this->maintenanceService->checkSecretToken($token)) {
                // Store token in session for subsequent requests
                session(['maintenance_bypass_token' => $token]);

                return $next($request);
            }
        }

        // Check for secret token bypass (session)
        if (session()->has('maintenance_bypass_token')) {
            $token = session('maintenance_bypass_token');
            if ($this->maintenanceService->checkSecretToken($token)) {
                return $next($request);
            } else {
                // Token no longer valid, remove from session
                session()->forget('maintenance_bypass_token');
            }
        }

        // Check user-based bypass (role-based for authenticated users)
        $user = Auth::user();
        if ($this->maintenanceService->canBypass($user)) {
            return $next($request);
        }

        // No bypass allowed - return maintenance page
        return $this->maintenanceResponse($type, $request);
    }

    /**
     * Return appropriate maintenance response based on type.
     */
    private function maintenanceResponse(MaintenanceType $type, Request $request): Response
    {
        $config = $this->maintenanceService->getConfig();

        // Determine view template based on type
        $view = match ($type) {
            MaintenanceType::PRELAUNCH => 'errors.maintenance-prelaunch',
            default => 'errors.maintenance-deployment',
        };

        // Prepare data for view
        $data = [
            'type' => $type,
            'config' => $config,
            'retry_after' => $type->retryAfter(),
        ];

        // Return 503 Service Unavailable
        return response()->view($view, $data, 503)
            ->header('Retry-After', (string) $type->retryAfter());
    }
}
