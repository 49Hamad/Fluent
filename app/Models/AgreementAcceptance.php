<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Immutable evidence of an electronic acceptance. */
class AgreementAcceptance extends Model
{
    protected $fillable = [
        'student_application_id', 'agreement_id', 'agreement_title', 'agreement_version',
        'content_snapshot', 'details_snapshot', 'content_hash', 'acceptance_statement',
        'accepted_at', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'details_snapshot' => 'array',
        'accepted_at' => 'datetime',
    ];

    protected $hidden = ['ip_address', 'user_agent'];

    protected static function booted(): void
    {
        static::updating(fn () => false);   // evidence is never edited
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(StudentApplication::class, 'student_application_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }
}
