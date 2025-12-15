<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInvoiceProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'company_name',
        'nip',
        'vat_id',
        'regon',
        'street',
        'street_number',
        'postal_code',
        'city',
        'country',
        'validated_at',
        'consent_given_at',
        'consent_ip',
        'consent_user_agent',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'consent_given_at' => 'datetime',
    ];

    /**
     * Relationship: User owns this invoice profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: Format NIP with dashes (XXX-XXX-XX-XX)
     * Mutator: Strip all non-digits when storing
     */
    protected function nip(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? $this->formatNIP($value) : null,
            set: fn ($value) => $value ? preg_replace('/[^0-9]/', '', $value) : null
        );
    }

    /**
     * Helper: Format NIP for display
     */
    private function formatNIP(string $nip): string
    {
        if (strlen($nip) !== 10) {
            return $nip;
        }

        return substr($nip, 0, 3).'-'.
               substr($nip, 3, 3).'-'.
               substr($nip, 6, 2).'-'.
               substr($nip, 8, 2);
    }

    /**
     * Helper: Get formatted address
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = [
            trim($this->street.' '.($this->street_number ?? '')),
            $this->postal_code.' '.$this->city,
            $this->country === 'PL' ? 'Polska' : $this->country,
        ];

        return implode(', ', array_filter($parts));
    }
}
