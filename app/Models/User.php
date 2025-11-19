<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'email_verified_at',
        'phone_e164',
        'street_name',
        'street_number',
        'city',
        'postal_code',
        'access_notes',
        'preferred_language',
        'sms_consent_given_at',
        'sms_consent_ip',
        'sms_consent_user_agent',
        'sms_opted_out_at',
        'sms_opt_out_method',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'sms_consent_given_at' => 'datetime',
            'sms_opted_out_at' => 'datetime',
        ];
    }

    /**
     * Check if user has given SMS consent and has not opted out.
     *
     * @return bool
     */
    public function hasSmsConsent(): bool
    {
        return $this->sms_consent_given_at !== null && $this->sms_opted_out_at === null;
    }

    /**
     * Grant SMS consent with tracking.
     *
     * @param string|null $ip IP address of consent
     * @param string|null $userAgent User agent string
     * @return void
     */
    public function grantSmsConsent(?string $ip = null, ?string $userAgent = null): void
    {
        $this->update([
            'sms_consent_given_at' => now(),
            'sms_consent_ip' => $ip,
            'sms_consent_user_agent' => $userAgent,
            'sms_opted_out_at' => null,
            'sms_opt_out_method' => null,
        ]);
    }

    /**
     * Revoke SMS consent (opt-out).
     *
     * @param string $method Opt-out method: 'manual', 'STOP_reply', 'admin'
     * @return void
     */
    public function revokeSmsConsent(string $method = 'manual'): void
    {
        $this->update([
            'sms_opted_out_at' => now(),
            'sms_opt_out_method' => $method,
        ]);
    }

    /**
     * Get the user's full name.
     * Returns concatenation of first_name and last_name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Get the user's full name via the 'name' attribute.
     *
     * This accessor provides a 'name' attribute by combining first_name and last_name.
     * Used throughout the application by notifications, emails, Blade templates, and API responses.
     *
     * Background: The User model stores names in two separate fields (first_name, last_name)
     * for flexibility, but many parts of the application expect a single 'name' property
     * (notifications, email templates, legacy code). This accessor bridges that gap without
     * requiring changes to all consuming code.
     *
     * @return string Full name (first_name + last_name)
     *
     * @example
     * $user->name         // Returns "Jan Kowalski" (via this accessor)
     * $user->full_name    // Returns "Jan Kowalski" (via getFullNameAttribute)
     * $user->first_name   // Returns "Jan"
     * $user->last_name    // Returns "Kowalski"
     *
     * @see getFullNameAttribute() - Canonical implementation of name concatenation
     * @see getFilamentName() - Used by Filament admin panel (also calls getFullNameAttribute)
     *
     * @since November 2025 - Added to support email notifications and templates
     */
    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Get the name to display in Filament (avatar, menu, etc.)
     * Required by Filament\Models\Contracts\HasName interface.
     *
     * @return string
     */
    public function getFilamentName(): string
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Get user's preferred language with fallback.
     *
     * @return string
     */
    public function getPreferredLanguageAttribute(?string $value): string
    {
        return $value ?? 'pl';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'staff']);
    }

    // Helper methods for role checking
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'super-admin']);
    }

    // Relationships
    public function staffAppointments()
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }

    public function customerAppointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function serviceAvailabilities()
    {
        return $this->hasMany(ServiceAvailability::class, 'user_id');
    }

    /**
     * Get the staff schedules (base weekly patterns) for this user.
     */
    public function staffSchedules()
    {
        return $this->hasMany(StaffSchedule::class, 'user_id');
    }

    /**
     * Get the date exceptions for this user.
     */
    public function dateExceptions()
    {
        return $this->hasMany(StaffDateException::class, 'user_id');
    }

    /**
     * Get the vacation periods for this user.
     */
    public function vacationPeriods()
    {
        return $this->hasMany(StaffVacationPeriod::class, 'user_id');
    }

    /**
     * Get the services that this staff member can perform.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_staff', 'user_id', 'service_id')
                    ->withTimestamps();
    }
}
