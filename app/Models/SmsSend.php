<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SMS Send Model
 *
 * Records of actual SMS sends with delivery status tracking.
 *
 * @property int $id
 * @property string $template_key FK to sms_templates.key
 * @property string $language Language code: 'pl', 'en'
 * @property string $phone_to Recipient phone number in international format
 * @property string $message_body Rendered SMS message content
 * @property string $status SMS delivery status: pending, sent, failed, invalid_number
 * @property \Illuminate\Support\Carbon|null $sent_at When SMS was sent
 * @property array|null $metadata User ID, appointment ID, etc.
 * @property string $message_key Idempotency key
 * @property string|null $error_message Error details if failed
 * @property string|null $sms_id SMSAPI message ID for tracking
 * @property int|null $message_length SMS character length
 * @property int|null $message_parts Number of SMS parts (multi-part messages)
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SmsSend extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sms_sends';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_key',
        'language',
        'phone_to',
        'message_body',
        'status',
        'sent_at',
        'metadata',
        'message_key',
        'error_message',
        'sms_id',
        'message_length',
        'message_parts',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'message_length' => 'integer',
        'message_parts' => 'integer',
    ];

    /**
     * Get the SMS template associated with this send.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function smsTemplate(): BelongsTo
    {
        return $this->belongsTo(SmsTemplate::class, 'template_key', 'key');
    }

    /**
     * Get all events for this SMS send.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function smsEvents(): HasMany
    {
        return $this->hasMany(SmsEvent::class);
    }

    /**
     * Scope a query to filter by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by recipient phone number.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $phone
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecipient($query, string $phone)
    {
        return $query->where('phone_to', $phone);
    }

    /**
     * Scope a query to only include SMS sent in the last 7 days.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query)
    {
        return $query->where('sent_at', '>=', now()->subDays(7));
    }

    /**
     * Mark the SMS as successfully sent.
     *
     * @param string|null $smsId SMSAPI message ID
     * @return void
     */
    public function markAsSent(?string $smsId = null): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sms_id' => $smsId,
        ]);
    }

    /**
     * Mark the SMS as failed with error message.
     *
     * @param string $error
     * @return void
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    /**
     * Mark the SMS as invalid number.
     *
     * @return void
     */
    public function markAsInvalidNumber(): void
    {
        $this->update([
            'status' => 'invalid_number',
        ]);
    }

    /**
     * Check if the SMS was sent successfully.
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Check if the SMS failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the SMS is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the phone number is invalid.
     *
     * @return bool
     */
    public function isInvalidNumber(): bool
    {
        return $this->status === 'invalid_number';
    }
}
