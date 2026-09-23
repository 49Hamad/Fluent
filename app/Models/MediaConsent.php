<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One media-consent decision. Append-only: the latest row is the current state. */
class MediaConsent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['student_application_id', 'granted', 'consent_text', 'consent_version', 'decided_at', 'ip_address', 'user_agent'];

    protected $casts = ['granted' => 'boolean', 'decided_at' => 'datetime'];

    protected $hidden = ['ip_address', 'user_agent'];

    protected static function booted(): void
    {
        static::updating(fn () => false);   // history is never edited
    }
}
