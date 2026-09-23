<?php

namespace App\Models;

use App\Enums\ChallengeStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BusinessChallenge extends Model
{
    public const FILE_DISK = 'challenges';

    protected $fillable = [
        'reference', 'status', 'status_changed_at',
        'org_name', 'org_type', 'org_type_other', 'sector', 'contact_name', 'job_title', 'email', 'phone',
        'challenge_description', 'affected_parties', 'current_impact', 'expected_outputs', 'expected_outputs_other',
        'can_share_materials', 'has_confidential_info', 'confidential_details',
        'file_path', 'file_original_name', 'file_size', 'consented_at', 'internal_notes',
    ];

    protected $casts = [
        'status' => ChallengeStatus::class,
        'status_changed_at' => 'datetime',
        'expected_outputs' => 'array',
        'can_share_materials' => 'boolean',
        'has_confidential_info' => 'boolean',
        'file_size' => 'integer',
        'consented_at' => 'datetime',
    ];

    /** Never exposed if this model is ever serialised. */
    protected $hidden = ['internal_notes', 'file_path'];

    protected static function booted(): void
    {
        // Remove the private file when a challenge is deleted.
        static::deleted(function (self $challenge) {
            if ($challenge->file_path) {
                Storage::disk(self::FILE_DISK)->delete($challenge->file_path);
            }
        });
    }
}
