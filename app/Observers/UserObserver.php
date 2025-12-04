<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     *
     * Clears all active sessions when user roles change to prevent
     * privilege escalation via existing sessions.
     */
    public function updated(User $user): void
    {
        // Check if roles relationship was modified
        // We need to manually track this since roles are in pivot table
        if ($user->wasChanged('updated_at')) {
            // Get current roles
            $currentRoles = $user->roles->pluck('name')->sort()->values()->toArray();

            // Get original roles from before the update
            // Note: This requires eager loading roles before update
            $originalRoles = collect($user->getOriginal('roles') ?? [])
                ->pluck('name')
                ->sort()
                ->values()
                ->toArray();

            // Compare role arrays
            if ($currentRoles !== $originalRoles) {
                // Roles have changed - clear all user sessions
                $deletedCount = DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();

                \Log::info('User sessions cleared due to role change', [
                    'user_id' => $user->id,
                    'user' => $user->email,
                    'old_roles' => $originalRoles,
                    'new_roles' => $currentRoles,
                    'sessions_cleared' => $deletedCount,
                ]);
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     *
     * Clears all sessions when user is deleted.
     */
    public function deleted(User $user): void
    {
        $deletedCount = DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        \Log::info('User sessions cleared due to account deletion', [
            'user_id' => $user->id,
            'user' => $user->email,
            'sessions_cleared' => $deletedCount,
        ]);
    }
}
